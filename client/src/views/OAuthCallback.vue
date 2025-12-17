<template>
  <div style="padding: 1.25rem; text-align: center">Signing you in...</div>
</template>

<script setup lang="ts">
import { onMounted } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";
import Swal from "sweetalert2";
import { useToast } from "vue-toastification";
const toast = useToast();

// ...existing code...
const router = useRouter();
axios.defaults.withCredentials = true;
axios.defaults.baseURL = "http://localhost:8000";

onMounted(async () => {
  // so the new URLSearchParams creates a Object and have method like getAll() .get() .has() that are use for the  url
  // and the location.search is just a read only property that is the query string of the url
  const params = new URLSearchParams(location.search);
  const ok = params.get("ok") === "1";
  const error = params.get("error");

  // Handle error cases first
  if (error) {
    if (error === "user_not_found") {
      toast.error("User not found. Please create an account to continue.");
      return void (await router.replace({ name: "signup" }));
    }

    if (error === "already_registered") {
      toast.info("Already registered. Sign in with your Google account.");
      return void (await router.replace({ name: "login" }));
    }

    if (error === "unverified_email") {
      toast.warning(
        "Email not verified. Verify your Google email and try again."
      );
      return void (await router.replace({ name: "login" }));
    }

    if (error === "server_error") {
      toast.error(
        "Something went wrong on our server. Please try again later."
      );
      return void (await router.replace({ name: "login" }));
    }

    toast.error("Sign-in failed. Please try again.");
    return void (await router.replace({ name: "login" }));
  }

  if (ok) {
    try {
      /* so the route replace is that it replace the current session history so the browser
     will not remember the former url the browser history eg chrome will be the new one that replace the former one 
     so the back button will not return to the replace url


     while the route href or the assign will create a new history so the will be a previos page */
      await router.replace({ name: "dashboard" });
    } catch {
      await router.replace({ name: "login" });
    }
  }
});
</script>

<style scoped></style>
