<template>
  <div class="login-container">
    <div class="form-card">
      <h2>Forgot Password</h2>
      <form @submit.prevent="submit">
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
        <button type="submit" class="login-btn" :disabled="userAuth.isLoading">
          <span v-if="!userAuth.isLoading">Send reset link</span>
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
import { userAuthStore } from "@/stores/UserStore";
const userAuth = userAuthStore();

const submit = async () => {
  userAuth.validateEmail();
  if (userAuth.error.email) return;
  if (!userAuth.email) return;
  await userAuth.requestPasswordReset(userAuth.email);
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
</style>
