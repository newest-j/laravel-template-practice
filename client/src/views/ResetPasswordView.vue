<template>
  <div class="login-container">
    <div class="form-card">
      <h2>Reset Password</h2>
      <form @submit.prevent="submit">
        <div class="form-group">
          <label>Email</label>
          <p class="readonly-value">{{ userAuth.email }}</p>
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
          <label for="password_confirmation">Confirm Password</label>
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

        <button type="submit" class="login-btn" :disabled="userAuth.isLoading">
          <span v-if="!userAuth.isLoading">Reset password</span>
          <span v-else>Processing...</span>
        </button>
        <p class="signup-link">
          <router-link :to="{ name: 'login' }">Back to login</router-link>
        </p>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { userAuthStore } from "@/stores/UserStore";

const userAuth = userAuthStore();
const route = useRoute();
const router = useRouter();

const token = ref<string>("");

onMounted(() => {
  // the String() Convert whatever comes from query into a definite string
  token.value = String(route.query.token || "");
  // prefill email from query
  const qEmail = route.query.email ? String(route.query.email) : "";
  if (qEmail) userAuth.email = qEmail;
});

const submit = async () => {
  // run validations
  userAuth.validatePassword();
  userAuth.validateConfirmPassword();

  if (!token.value || !userAuth.email) return;
  if (userAuth.error.password || userAuth.error.confirmPassword) return;

  const ok = await userAuth.performPasswordReset({
    token: token.value,
    email: userAuth.email,
    password: userAuth.password,
    password_confirmation: userAuth.confirmPassword,
  });

  if (ok) {
    // optional: clear sensitive fields
    userAuth.password = "";
    userAuth.confirmPassword = "";
    router.replace({ name: "login" });
  }
};
</script>

<style scoped>
/* reuse styles from LoginView.vue if desired */
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
  max-width: 400px;
  width: 100%;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
}

h2 {
  text-align: center;
  margin-bottom: 1.5rem;
  color: #333;
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
  outline: none;
  transition: border 0.3s;
}

input:focus {
  border-color: #4f46e5;
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  font-size: 0.9rem;
}

.remember {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  color: #555;
}

.forgot-link {
  color: #4f46e5;
  text-decoration: none;
}

.forgot-link:hover {
  text-decoration: underline;
}

.login-btn {
  width: 100%;
  padding: 0.8rem;
  background: #4f46e5;
  color: #fff;
  font-weight: 600;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.3s;
}
.google-btn {
  cursor: pointer;
}

.login-btn:hover {
  background: #4338ca;
}

.signup-link {
  text-align: center;
  margin-top: 1rem;
  font-size: 0.9rem;
  color: #555;
}

.signup-link a {
  color: #4f46e5;
  text-decoration: none;
}

.signup-link a:hover {
  text-decoration: underline;
}

.err {
  color: #d93025;
  font-size: 0.75rem;
  display: block;
  margin-top: 0.25rem;
}
button[disabled] {
  opacity: 0.7;
  cursor: not-allowed;
}
.readonly-value {
  padding: 0.7rem;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  color: #374151;
  margin: 0;
}
</style>
