<template>
  <div class="payment-result">
    <div class="result-card">
      <!-- Loading State -->
      <div v-if="paymentStore.isLoading" class="loading-state">
        <div class="spinner-large"></div>
        <h2>Verifying Payment...</h2>
        <p>Please wait while we confirm your payment</p>
      </div>

      <!-- Success State -->
      <div
        v-else-if="paymentStore.transactionStatus === 'success'"
        class="success-state"
      >
        <div class="success-icon">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="10" fill="#10b981" />
            <path
              d="M8 12l2 2 4-4"
              stroke="white"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </div>
        <h2>Payment Successful!</h2>
        <p class="success-message">
          Your payment has been processed successfully. Thank you for your
          purchase!
        </p>

        <div class="payment-details" v-if="hasReceiptData">
          <h3>Transaction Details</h3>
          <div class="detail-row">
            <span>Amount:</span>
            <strong>{{
              formatCurrency(
                paymentStore.paymentData.amount,
                paymentStore.paymentData.currency
              )
            }}</strong>
          </div>
          <div class="detail-row" v-if="paymentStore.paymentData.tx_ref">
            <span>Reference:</span>
            <span class="reference">{{ paymentStore.paymentData.tx_ref }}</span>
          </div>
          <div
            class="detail-row"
            v-if="paymentStore.paymentData.transaction_id"
          >
            <span>Transaction ID:</span>
            <span class="reference">{{
              paymentStore.paymentData.transaction_id
            }}</span>
          </div>
          <div class="detail-row">
            <span>Date:</span>
            <span>{{ formatDate(new Date()) }}</span>
          </div>
        </div>

        <div class="action-buttons">
          <button
            @click="downloadReceipt"
            class="secondary-btn"
            v-if="hasReceiptData"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
              <path
                d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"
                stroke="currentColor"
                stroke-width="2"
              />
              <polyline
                points="7,10 12,15 17,10"
                stroke="currentColor"
                stroke-width="2"
              />
              <line
                x1="12"
                y1="15"
                x2="12"
                y2="3"
                stroke="currentColor"
                stroke-width="2"
              />
            </svg>
            Download Receipt
          </button>
          <button @click="goToDashboard" class="primary-btn">
            Continue to Dashboard
          </button>
        </div>
      </div>

      <!-- Failed State -->
      <div
        v-else-if="paymentStore.transactionStatus === 'failed'"
        class="error-state"
      >
        <div class="error-icon">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="10" fill="#ef4444" />
            <line
              x1="15"
              y1="9"
              x2="9"
              y2="15"
              stroke="white"
              stroke-width="2"
            />
            <line
              x1="9"
              y1="9"
              x2="15"
              y2="15"
              stroke="white"
              stroke-width="2"
            />
          </svg>
        </div>
        <h2>Payment Failed</h2>
        <p class="error-message">
          {{
            paymentStore.error ||
            "Your payment could not be processed. Please try again."
          }}
        </p>

        <div class="action-buttons">
          <button @click="tryAgain" class="primary-btn">Try Again</button>
          <button @click="goToDashboard" class="secondary-btn">
            Back to Dashboard
          </button>
        </div>
      </div>

      <!-- Unknown/Error State -->
      <div v-else class="error-state">
        <div class="error-icon">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none">
            <circle
              cx="12"
              cy="12"
              r="10"
              stroke="#ef4444"
              stroke-width="2"
              fill="none"
            />
            <line
              x1="12"
              y1="8"
              x2="12"
              y2="12"
              stroke="#ef4444"
              stroke-width="2"
            />
            <line
              x1="12"
              y1="16"
              x2="12.01"
              y2="16"
              stroke="#ef4444"
              stroke-width="2"
            />
          </svg>
        </div>
        <h2>Payment Status Unknown</h2>
        <p class="error-message">
          We couldn't verify your payment status. Please contact support if you
          were charged.
        </p>

        <div class="action-buttons">
          <button
            @click="checkAgain"
            class="primary-btn"
            :disabled="paymentStore.isLoading"
          >
            Check Again
          </button>
          <button @click="goToDashboard" class="secondary-btn">
            Back to Dashboard
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import { usePaymentStore } from "@/stores/PaymentStore";

const route = useRoute();
const router = useRouter();
const paymentStore = usePaymentStore();

onMounted(async () => {
  const status = (route.query.status as string) || undefined;
  const transactionId = route.query.transaction_id as string | undefined;

  // Set initial UI state from status query if present
  if (status === "success") {
    paymentStore.transactionStatus = "success";
  } else if (status === "failed") {
    paymentStore.transactionStatus = "failed";
  }

  // If transaction id exists, fetch full transaction details
  if (transactionId) {
    await paymentStore.getTrasactionDetails(transactionId);
    await paymentStore.checkSubscription(transactionId);
  }
});

// Single source of truth for when receipt/details can show
const hasReceiptData = computed(() => {
  const d = paymentStore.paymentData as any;
  return !!d && (d.tx_ref || d.transaction_id || d.amount !== undefined);
});

const hasSubscription = computed(() => {
  const subscription = paymentStore.subscriptionData as any;
  return !!subscription;
});

// Intl.NumberFormat It defines how numbers should be formatted for display based on locale + options.
// it also have the resolveOptions {
//   "locale": "en-NG",
//   "style": "currency",
//   "currency": "NGN",
//   "numberingSystem": "latn",
//   "minimumFractionDigits": 2,
//   "maximumFractionDigits": 2
// }
const formatCurrency = (amount: number, currency: string) => {
  return new Intl.NumberFormat("en-NG", {
    style: "currency",
    currency: currency || "NGN",
  }).format(amount);
};

const formatDate = (date: Date) => {
  return date.toLocaleDateString("en-NG", {
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

const downloadReceipt = () => {
  // Generate and download a receipt
  const d: any = paymentStore.paymentData || {};
  const receiptData = {
    amount: Number(d.amount) || 0,
    currency: d.currency || "NGN",
    tx_ref: d.tx_ref || d.reference || d.ref || "N/A",
    transaction_id: d.transaction_id || d.transactionId || "N/A",
    date: new Date().toISOString(),
  };

  const receiptText = `
PAYMENT RECEIPT
===============
Amount: ${formatCurrency(receiptData.amount, receiptData.currency)}
Reference: ${receiptData.tx_ref}
Transaction ID: ${receiptData.transaction_id}
Date: ${formatDate(new Date())}
Status: Successful
===============
Thank you for your payment!
  `;

  //   blob is Binary Large Object
  // A file-like object stored in browser memory
  const blob = new Blob([receiptText], { type: "text/plain" });
  //   createObjectURL() is to Creates a temporary local URL
  // Points to the Blob in memory
  const url = URL.createObjectURL(blob);
  // the createElement is use to create html element tags like a
  // You create an <a> tag in JavaScript only
  const a = document.createElement("a");
  a.href = url;
  // and then the download with the a is to download the file
  // Forces the browser to download instead of opening
  a.download = `receipt_${receiptData.tx_ref}.txt`;
  // though there is a click in the vue component that just calls the downloadReceipt
  // what the a.click does is that it tell the browser to act like a user
  // click a download link
  a.click();

  // URL.revokeObjectURL(url); Releases the memory used by the Blob
  // Prevents memory leaks
  // The file is already downloaded, so safe to revoke
  URL.revokeObjectURL(url);
};

const goToDashboard = async () => {
  if (hasSubscription) {
    await router.replace("/user");
  } else {
    await router.push("/dashboard");
  }
  paymentStore.resetPayment();
};

const tryAgain = async () => {
  await router.push("/dashboard");
  paymentStore.resetPayment();
};

const checkAgain = async () => {
  const transactionId = route.query.transaction_id as string;
  if (transactionId) {
    await paymentStore.verifyPayment(transactionId);
  }
};
</script>

<style scoped>
/* =========================
   Layout
========================= */
.payment-result {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  padding: 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.result-card {
  background: white;
  padding: 3rem;
  border-radius: 16px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1),
    0 10px 10px -5px rgba(0, 0, 0, 0.04);
  width: 100%;
  max-width: 500px;
  text-align: center;
}

/* =========================
   States
========================= */
.loading-state,
.success-state,
.error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.5rem;
}

.success-icon,
.error-icon {
  margin-bottom: 1rem;
}

/* =========================
   Spinner
========================= */
.spinner-large {
  width: 64px;
  height: 64px;
  border: 4px solid rgba(102, 126, 234, 0.3);
  border-radius: 50%;
  border-top-color: #667eea;
  animation: spin 1s ease-in-out infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* =========================
   Typography
========================= */
h2 {
  margin: 0;
  color: #1f2937;
  font-size: 1.8rem;
  font-weight: 600;
}

.success-message,
.error-message {
  font-size: 1.1rem;
  line-height: 1.6;
  margin: 0;
}

.success-message {
  color: #059669;
}

.error-message {
  color: #dc2626;
}

/* =========================
   Payment Details
========================= */
.payment-details {
  background: #f9fafb;
  padding: 1.5rem;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  width: 100%;
  text-align: left;
  margin: 1rem 0;
}

.payment-details h3 {
  margin: 0 0 1rem 0;
  color: #374151;
  font-size: 1.1rem;
  text-align: center;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f3f4f6;
  font-size: 0.95rem;
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-row span:first-child {
  color: #6b7280;
  font-weight: 500;
}

.reference {
  font-family: monospace;
  font-size: 0.85rem;
  background: #f3f4f6;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
}

/* =========================
   Actions
========================= */
.action-buttons {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
  width: 100%;
}

.primary-btn,
.secondary-btn {
  flex: 1;
  padding: 0.875rem 1.5rem;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  border: none;
}

.primary-btn {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.primary-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
}

.secondary-btn {
  background: #f9fafb;
  color: #374151;
  border: 1px solid #e5e7eb;
}

.secondary-btn:hover {
  background: #f3f4f6;
  transform: translateY(-1px);
}

.primary-btn:disabled,
.secondary-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.flow-indicator {
  font-size: 0.85rem;
  color: #6b7280;
}

/* =========================
   Responsive
========================= */
@media (max-width: 640px) {
  .payment-result {
    padding: 1rem;
  }

  .result-card {
    padding: 2rem 1.5rem;
  }

  .action-buttons {
    flex-direction: column;
  }

  h2 {
    font-size: 1.5rem;
  }
}
</style>
