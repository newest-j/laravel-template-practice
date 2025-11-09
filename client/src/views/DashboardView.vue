<template>
  <div>
    <div>
      Welcome {{ userAuth.currentUser?.name }}

      <!-- Added: show verified timestamp -->
      <p v-if="userAuth.currentUser?.email_verified_at" class="verified-info">
        Verified at:
        {{ userAuth.formatTs(userAuth.currentUser.email_verified_at) }}
        <small>({{ userAuth.activeTimezone }})</small>
      </p>

      <button
        @click="onLogout"
        class="google-btn"
        :disabled="userAuth.isLoading"
      >
        <span v-if="!userAuth.isLoading">Logout</span>
        <span v-else>Logging out...</span>
      </button>
    </div>

    <div class="login-container">
      <div class="form-card">
        <h2>Change Password</h2>

        <form @submit.prevent="submit">
          <div class="form-group">
            <label for="current_password">Current Password</label>
            <input
              id="current_password"
              type="password"
              v-model.trim="currentPassword"
              autocomplete="current-password"
            />
            <span v-if="currentError" class="err">{{ currentError }}</span>
          </div>

          <div class="form-group">
            <label for="password">New Password</label>
            <input
              id="password"
              type="password"
              v-model="userAuth.password"
              @blur="userAuth.validatePassword()"
              autocomplete="new-password"
            />
            <span v-if="userAuth.error.password" class="err">{{
              userAuth.error.password
            }}</span>
          </div>

          <div class="form-group">
            <label for="password_confirmation">Confirm New Password</label>
            <input
              id="password_confirmation"
              type="password"
              v-model="userAuth.confirmPassword"
              @blur="userAuth.validateConfirmPassword()"
              autocomplete="new-password"
            />
            <span v-if="userAuth.error.confirmPassword" class="err">{{
              userAuth.error.confirmPassword
            }}</span>
          </div>

          <button
            type="submit"
            class="login-btn"
            :disabled="userAuth.isLoading"
          >
            <span v-if="!userAuth.isLoading">Update password</span>
            <span v-else>Processing...</span>
          </button>
        </form>
      </div>
    </div>

    <div class="login-container">
      <div class="form-card">
        <h2>Update Profile</h2>
        <form @submit.prevent="submitprofileupdate">
          <div class="form-group">
            <label for="name">Full Name</label>
            <input
              id="name"
              type="text"
              v-model.trim="userAuth.name"
              @blur="userAuth.validateName()"
              autocomplete="name"
            />
            <span v-if="userAuth.error.name" class="err">{{
              userAuth.error.name
            }}</span>
          </div>

          <div class="form-group">
            <label for="email">Email Address</label>
            <input
              id="email"
              type="email"
              v-model.trim="userAuth.email"
              @blur="userAuth.validateEmail()"
              autocomplete="email"
            />
            <span v-if="userAuth.error.email" class="err">{{
              userAuth.error.email
            }}</span>
          </div>

          <button
            type="submit"
            class="login-btn"
            :disabled="userAuth.isLoading"
          >
            <span v-if="!userAuth.isLoading">Save changes</span>
            <span v-else>Saving...</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { userAuthStore } from "../stores/UserStore";
import { useRouter } from "vue-router";
const userAuth = userAuthStore();
// the userouter is for the router instance itself like to modify or navigate route e.g push or replace
// whle the useroute is use for infor about the current route e.g params path query
const router = useRouter();
const currentPassword = ref<string>("");
const currentError = ref<string>("");

const onLogout = async () => {
  await userAuth.logout(router);
};

const submit = async () => {
  // simple current password check
  currentError.value = currentPassword.value
    ? ""
    : "Current password is required";

  userAuth.validatePassword();
  userAuth.validateConfirmPassword();

  if (
    currentError.value ||
    userAuth.error.password ||
    userAuth.error.confirmPassword
  )
    return;

  const ok = await userAuth.changePassword({
    current_password: currentPassword.value,
    password: userAuth.password,
    password_confirmation: userAuth.confirmPassword,
  });

  if (ok) {
    // clear sensitive fields
    currentPassword.value = "";
    userAuth.password = "";
    userAuth.confirmPassword = "";
  }
};

// Prefill from currentUser if available
if (userAuth.currentUser) {
  if (!userAuth.name) userAuth.name = userAuth.currentUser.name;
  if (!userAuth.email) userAuth.email = userAuth.currentUser.email;
}

const submitprofileupdate = async () => {
  userAuth.validateName();
  userAuth.validateEmail();
  if (userAuth.error.name || userAuth.error.email) return;

  const ok = await userAuth.changeprofileinformation({
    name: userAuth.name,
    email: userAuth.email,
  } as any);

  if (ok && userAuth.currentUser) {
    userAuth.currentUser.name = userAuth.name;
    userAuth.currentUser.email = userAuth.email;
  }
};
</script>

<style scoped>
.google-btn {
  cursor: pointer;
}
.verified-info {
  font-size: 14px;
  color: #2e7d32;
  margin: 8px 0;
}
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: #f5f7fa;
  padding: 1rem;
}
.form-card {
  background: #fff;
  padding: 2rem;
  border-radius: 12px;
  max-width: 420px;
  width: 100%;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
}
.form-group {
  margin-bottom: 1rem;
}
label {
  display: block;
  margin-bottom: 0.4rem;
  font-weight: 600;
  color: #444;
}
input {
  width: 100%;
  padding: 0.7rem;
  border: 1px solid #ccc;
  border-radius: 8px;
}
input:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}
.login-btn {
  width: 100%;
  padding: 0.8rem 1rem;
  border: none;
  border-radius: 8px;
  background: #2563eb;
  color: #fff;
  font-weight: 600;
  cursor: pointer;
}
.err {
  color: #b91c1c;
  font-size: 0.9rem;
}
button[disabled] {
  opacity: 0.7;
  cursor: not-allowed;
}
</style>
