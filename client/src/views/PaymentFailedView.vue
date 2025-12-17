<template>
  <div class="payment-status">
    <div class="card error">
      <h2>Payment Failed</h2>
      <p>We couldn't confirm your payment.</p>
      <div class="actions">
        <button class="primary" @click="tryAgain">Try Again</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from "vue-router";
const router = useRouter();
const tryAgain = () => router.push("/dashboard");
</script>

<style scoped>
.payment-status {
  min-height: 60vh;
  display: flex;
  align-items: center;
  justify-content: center;
}
.card {
  background: #fff;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
  text-align: center;
}
.error h2 {
  color: #dc2626;
}
.actions {
  margin-top: 1rem;
}
.primary {
  background: #2563eb;
  color: #fff;
  border: none;
  padding: 0.75rem 1.25rem;
  border-radius: 8px;
  cursor: pointer;
}
</style>
