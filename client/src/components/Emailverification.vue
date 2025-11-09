<template>
  <div>
    <div v-if="userAuth.needsVerification" class="verify-banner">
      <p>Please verify your email ({{ userAuth.currentUser?.email }}).</p>
      <button
        :disabled="!userAuth.canResend || userAuth.resend.sending"
        @click="userAuth.resendVerification()"
      >
        {{
          userAuth.resend.sending
            ? "Sending..."
            : userAuth.canResend
            ? "Resend email"
            : "Wait..."
        }}
      </button>
      <span v-if="userAuth.resend.lastSentAt" class="hint">
        Sent {{ Math.round((Date.now() - userAuth.resend.lastSentAt) / 1000) }}s
        ago
      </span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { userAuthStore } from "../stores/UserStore";
const userAuth = userAuthStore();
</script>

<style scoped>
.verify-banner {
  background: #fff3cd;
  border: 1px solid #ffeeba;
  padding: 12px 16px;
  border-radius: 4px;
  font-size: 14px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
button {
  width: max-content;
  padding: 6px 12px;
}
.hint {
  font-size: 12px;
  color: #555;
}
</style>
