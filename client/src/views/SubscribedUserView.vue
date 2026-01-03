<template>
  <div>
    <h1>Welcome</h1>
    <p v-if="subscription">Subscription active until {{ expiresAt }}</p>
  </div>
</template>

<script setup lang="ts">
import { usePaymentStore } from "@/stores/PaymentStore";
import { computed } from "vue";
const PaymentStore = usePaymentStore();

const subscription = computed<any>(() => PaymentStore.subscriptionData);
const expiresAt = computed(() => subscription.value?.expires_at);
</script>

<style scoped></style>
