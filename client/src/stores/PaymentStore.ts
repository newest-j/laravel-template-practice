import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { paymentService } from "@/services/api.ts";
import type Payment from "@/Typescript/Payment";

export const usePaymentStore = defineStore("payment", () => {
  // State
  const isLoading = ref(false);
  const error = ref<string | null>(null);
  const paymentData = ref<any>(null);
  const transactionStatus = ref<string | null>(null);
  const flowType = ref<"redirect" | null>(null);
  const planId = ref<number | null>(null);

  // Form data (frontend only sends these; backend handles the rest)
  const customerEmail = ref<string>("");
  const customerName = ref<string>("");

  // Computed

  // Actions
  const clearError = () => {
    error.value = null;
  };

  const resetPayment = () => {
    paymentData.value = null;
    transactionStatus.value = null;
    error.value = null;
    flowType.value = null;
    planId.value = null;
  };

  const initializePayment = async () => {
    isLoading.value = true;

    try {
      const payload: Payment = {
        plan_id: planId.value,
        customer_email: customerEmail.value,
        customer_name: customerName.value,
      };

      const response = await paymentService.initializePayment(payload);
      paymentData.value = response;

      // Support different response shapes from backend
      const link =
        response?.link ||
        response?.data?.link ||
        response?.redirect_url ||
        response?.redirect; // backend returns 'redirect'

      // Redirect to payment page provided by backend
      if (link) {
        flowType.value = "redirect";
        window.location.href = link;
        return true;
      } else {
        throw new Error("Payment link not received");
      }
    } catch (err: any) {
      error.value = err.message || "Payment initialization failed";
      return false;
    } finally {
      isLoading.value = false;
    }
  };

  const verifyPayment = async (transactionId: string) => {
    isLoading.value = true;
    clearError();

    try {
      const response = await paymentService.verifyPayment(transactionId);
      transactionStatus.value = response?.status || "unknown";
      paymentData.value = response;
      return response;
    } catch (err: any) {
      error.value = err.message || "Payment verification failed";
      transactionStatus.value = "failed";
      return null;
    } finally {
      isLoading.value = false;
    }
  };

  return {
    // State
    isLoading,
    error,
    paymentData,
    transactionStatus,
    flowType,
    planId,

    // Form data
    customerEmail,
    customerName,

    // Computed

    // Actions
    clearError,
    resetPayment,
    initializePayment,
    verifyPayment,
  };
});
