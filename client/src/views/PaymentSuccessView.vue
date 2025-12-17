<template>
  <div class="payment-status">
    <div class="card success">
      <h2>Payment Successful</h2>
      <p>Your payment has been confirmed.</p>
      <div class="actions">
        <button class="primary" @click="goToDashboard">Go to Dashboard</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from "vue-router";
const router = useRouter();
const goToDashboard = () => router.push("/dashboard");
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
.success h2 {
  color: #059669;
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
