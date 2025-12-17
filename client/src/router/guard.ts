import type { Router } from "vue-router";
import { useUserAuthStore } from "@/stores/UserStore";

let authChecked = false;

export function registerAuthGuards(router: Router) {
  router.beforeEach(async (to) => {
    const store = useUserAuthStore();

    if (!authChecked) {
      await store.fetchCurrentUser();
      authChecked = true;
    }

    const isAuthed = store.isAuthenticated;
    const isVerified = !!store.currentUser?.email_verified_at;

    // if the user is not auth and trying to acccess a requireauth show the login
    if (to.meta.requiresAuth && !isAuthed) {
      return { name: "login" };
    }

    // If logged in but not verified, force them to the verify page

    if (
      to.meta.requiresAuth &&
      isAuthed &&
      !isVerified &&
      to.name !== "verify-email"
    ) {
      return { name: "verify-email" };
    }

    // If already verified, keep them off the verify page
    if (to.name === "verify-email" && isVerified) {
      return { name: "dashboard" };
    }

    // if the user is auth and trying to route to a guestonly show the dashboard
    //Guest-only pages: redirect authenticated users
    // the meta is a custom object where i can store extra info
    if (to.meta.guestOnly && isAuthed) {
      return isVerified ? { name: "dashboard" } : { name: "verify-email" };
    }
  });
}
