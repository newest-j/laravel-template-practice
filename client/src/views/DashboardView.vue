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

    <!-- Payment Section -->
    <div class="login-container">
      <div class="form-card">
        <h2>
          <svg
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            style="margin-right: 0.5rem"
          >
            <rect
              x="1"
              y="4"
              width="22"
              height="16"
              rx="2"
              ry="2"
              stroke="currentColor"
              stroke-width="2"
            />
            <line
              x1="1"
              y1="10"
              x2="23"
              y2="10"
              stroke="currentColor"
              stroke-width="2"
            />
          </svg>
          Make a Payment
        </h2>

        <div class="payment-section">
          <div v-if="paymentStore.isLoading" class="overlay">
            <div class="spinner-large"></div>
            <p class="overlay-text">Initializing payment...</p>
          </div>
          <p class="payment-description">
            Quick payment for subscription or services
          </p>

          <div class="quick-payment-options">
            <div class="payment-option" @click="makeQuickPayment(5000)">
              <div class="payment-amount">₦5,000</div>
              <div class="payment-label">Basic Plan</div>
            </div>
            <div class="payment-option" @click="makeQuickPayment(10000)">
              <div class="payment-amount">₦10,000</div>
              <div class="payment-label">Pro Plan</div>
            </div>
            <div class="payment-option" @click="makeQuickPayment(25000)">
              <div class="payment-amount">₦25,000</div>
              <div class="payment-label">Standard Plan</div>
            </div>
            <div class="payment-option" @click="makeQuickPayment(12000)">
              <div class="payment-amount">₦12,000</div>
              <div class="payment-label">Starter Plan</div>
            </div>
            <div class="payment-option" @click="makeQuickPayment(40000)">
              <div class="payment-amount">₦40,000</div>
              <div class="payment-label">Enterprise Plan</div>
            </div>
          </div>
        </div>
      </div>
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
import { useUserAuthStore } from "../stores/UserStore";
import { usePaymentStore } from "@/stores/PaymentStore";
import { useRouter } from "vue-router";

const userAuth = useUserAuthStore();
const paymentStore = usePaymentStore();
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

  const ok = await userAuth.changeProfileInformation({
    name: userAuth.name,
    email: userAuth.email,
  });

  if (ok && userAuth.currentUser) {
    userAuth.currentUser.name = userAuth.name;
    userAuth.currentUser.email = userAuth.email;
  }
};

// Payment functions
const makeQuickPayment = async (amount: number) => {
  // Map quick amounts to backend plan IDs (example mapping)
  // Ensure these IDs exist in the plans table
  // the record is used for the type declaration for an Object
  // just like the interface it just does not need the key to be
  // expicitly define
  const mapping: Record<number, number> = {
    5000: 1, // Basic
    10000: 2, // Pro
    25000: 3, // Standard
    40000: 4, // Enterprise
    12000: 5, // Starter
  };

  paymentStore.planId = mapping[amount] ?? null;
  paymentStore.customerEmail = userAuth.currentUser?.email || "";
  paymentStore.customerName = userAuth.currentUser?.name || "";
  // const planName =
  //   amount === 5000
  //     ? "Basic"
  //     : amount === 10000
  //     ? "Pro"
  //     : amount === 25000
  //     ? "Standard"
  //     : amount === 40000
  //     ? "Enterprise"
  //     : amount === 12000
  //     ? "Starter"
  //     : "Plan";
  // Frontend no longer sends description; backend derives it

  const success = await paymentStore.initializePayment();
  if (!success) {
    console.error("Quick payment initialization failed");
  }
};

// Custom payment page removed; dashboard offers quick plan options only.
</script>

<style scoped>
/* =========================
   General / Auth UI
========================= */
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

/* =========================
   Payment Section
========================= */
.payment-section {
  position: relative; /* needed for overlay */
  text-align: center;
}

.payment-description {
  color: #6b7280;
  margin-bottom: 1.5rem;
  font-size: 0.95rem;
}

.quick-payment-options {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  flex-wrap: wrap;
}

.payment-option {
  flex: 1;
  min-width: 120px;
  padding: 1rem 0.5rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  background: #f9fafb;
}

.payment-option:hover {
  border-color: #667eea;
  background: #f0f4ff;
  transform: translateY(-2px);
}

.payment-amount {
  font-size: 1.2rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.payment-label {
  font-size: 0.8rem;
  color: #6b7280;
  font-weight: 500;
}

.payment-actions {
  display: flex;
  justify-content: center;
}

.payment-btn {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.payment-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
}

h2 {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #1f2937;
  margin-bottom: 1.5rem;
  font-size: 1.25rem;
}

/* =========================
   Loading Overlay
========================= */
.overlay {
  position: absolute;
  inset: 0;
  background: rgba(255, 255, 255, 0.85);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  z-index: 10;
  border-radius: 12px;
}

.spinner-large {
  width: 64px;
  height: 64px;
  border: 4px solid rgba(37, 99, 235, 0.25);
  border-top-color: #2563eb;
  border-radius: 50%;
  animation: spin 0.9s ease-in-out infinite;
}

.overlay-text {
  color: #374151;
  font-weight: 500;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* =========================
   Responsive
========================= */
@media (max-width: 640px) {
  .quick-payment-options {
    flex-direction: column;
  }

  .payment-option {
    flex: none;
  }
}
</style>
