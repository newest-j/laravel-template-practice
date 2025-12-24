import { createRouter, createWebHistory } from "vue-router";
import HomeView from "@/views/SignupView.vue";
import OAuthCallback from "@/views/OAuthCallback.vue";
import VerifyEmailView from "@/views/VerifyEmailView.vue";
import { registerAuthGuards } from "./guard";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/",
      name: "signup",
      component: HomeView,
      meta: { guestOnly: true },
    },
    {
      path: "/login",
      name: "login",
      component: () => import("@/views/LoginView.vue"),
      meta: { guestOnly: true },
    },
    {
      path: "/verify-email",
      name: "verify-email",
      component: VerifyEmailView,
      meta: { requiresAuth: true },
    },
    {
      path: "/dashboard",
      name: "dashboard",
      component: () => import("@/views/DashboardView.vue"),
      meta: { requiresAuth: true },
    },

    {
      //   //this part is from the backend socialauth where the oauth/callbackis appended to the spa
      path: "/oauth/callback",
      name: "oauth-callback",
      component: OAuthCallback,
      meta: { guestOnly: true },
    },
    {
      path: "/forgot-password",
      name: "forgot-password",
      component: () => import("@/views/ForgotPasswordView.vue"),
      meta: { guestOnly: true },
    },
    {
      path: "/reset-password",
      name: "reset-password",
      component: () => import("@/views/ResetPasswordView.vue"),
      meta: { guestOnly: true },
    },
    {
      path: "/payment/result",
      name: "payment-result",
      component: () => import("@/views/PaymentResultView.vue"),
      meta: { requiresAuth: true },
    },
  ],
});

registerAuthGuards(router);
export default router;
