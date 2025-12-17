import { defineStore } from "pinia";
import { ref, reactive, computed } from "vue";
import Swal from "sweetalert2";
import { useToast } from "vue-toastification";
import type UserSignup from "@/Typescript/UserSignup";
import type CurrentUser from "@/Typescript/CurrentUser";
import { authuser } from "@/services/api.js";
import { browserTimeZone, formatTimestamp } from "@/utils/Datetime";

export const useUserAuthStore = defineStore("authstore", () => {
  // ===== State =====
  const name = ref("");
  const email = ref("");
  const password = ref("");
  const confirmPassword = ref("");
  const isLoading = ref(false);
  const resend = reactive({
    sending: false,
    lastSentAt: null as number | null,
  });
  const timezone = ref(browserTimeZone());

  const currentUser = ref<null | CurrentUser>(null);

  const error = reactive({
    name: "",
    email: "",
    password: "",
    confirmPassword: "",
  });

  // ===== Getters =====
  const isPasswordMatching = computed<boolean>(
    () => password.value === confirmPassword.value
  );
  const isAuthenticated = computed<boolean>(() => !!currentUser.value);
  const isVerified = computed<boolean>(
    () => !!currentUser.value?.email_verified_at
  );
  const needsVerification = computed<boolean>(
    () => isAuthenticated.value && !isVerified.value
  );
  const canResend = computed<boolean>(() => {
    if (!needsVerification.value) return false;
    if (!resend.lastSentAt) return true;
    return Date.now() - resend.lastSentAt > 60000; // 60s throttle
  });
  const activeTimezone = computed<string>(
    () => timezone.value || browserTimeZone()
  );

  // ===== Actions =====
  const validateName = () => {
    const fullNameRegex = /^[A-Za-z]{2,}(?: [A-Za-z]{2,})+$/;
    if (!name.value.trim()) error.name = "Name is required";
    else if (!fullNameRegex.test(name.value.trim()))
      error.name =
        "Please enter your full name (first and last name, letters only)";
    else error.name = "";
  };

  const validateEmail = () => {
    const emailReg =
      /^((\+?[0-9\s\-().]{7,})|([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}))$/;
    if (!email.value.trim()) error.email = "Email is required";
    else if (!emailReg.test(email.value.trim()))
      error.email = "Please enter a valid email address";
    else error.email = "";
  };

  const validatePassword = () => {
    const passwordReg =
      /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).{8,20}$/;
    if (!password.value.trim()) error.password = "Password is required";
    else if (!passwordReg.test(password.value.trim()))
      error.password =
        "Password min 8 and max 20 character at least one uppercase,lowercase,digit,and special character";
    else error.password = "";
  };

  const validateConfirmPassword = () => {
    if (!confirmPassword.value.trim())
      error.confirmPassword = "Please confirm your password";
    else if (!isPasswordMatching.value)
      error.confirmPassword = "The passwords do not match";
    else error.confirmPassword = "";
  };

  const fetchCurrentUser = async () => {
    try {
      const data = await authuser.me();
      currentUser.value = data;
      return data;
    } catch {
      currentUser.value = null;
      return null;
    }
  };

  const resendVerification = async () => {
    if (!canResend.value) return false;
    resend.sending = true;
    try {
      await authuser.resendVerification();
      resend.lastSentAt = Date.now();
    } finally {
      resend.sending = false;
    }
  };

  const setTimezone = (tz: string) => {
    timezone.value = tz;
  };

  const formatTs = (iso?: string | null, opts?: Intl.DateTimeFormatOptions) => {
    return formatTimestamp(iso, activeTimezone.value, opts);
  };

  const createUser = async (userData: UserSignup, router: any) => {
    const toast = useToast();
    isLoading.value = true;
    try {
      const postResponse = await authuser.signup(userData);
      if (postResponse) {
        currentUser.value = postResponse;
        toast.success("Account created successfully!");

        // reset form
        name.value = "";
        password.value = "";
        email.value = "";
        confirmPassword.value = "";

        Object.keys(error).forEach((key) => ((error as any)[key] = ""));

        if (router) router.replace("/dashboard");
        return postResponse;
      }
    } catch (err: any) {
      console.error("Error creating user:", err);
      toast.error("Failed to create user. " + (err.message || ""));
      return false;
    } finally {
      isLoading.value = false;
    }
  };

  const handleSubmit = async (router: any) => {
    const toast = useToast();
    isLoading.value = true;
    try {
      validateName();
      validateEmail();
      validatePassword();
      validateConfirmPassword();

      if (
        !error.name &&
        !error.email &&
        !error.password &&
        !error.confirmPassword
      ) {
        const result = await createUser(
          {
            name: name.value,
            email: email.value,
            password: password.value,
            password_confirmation: confirmPassword.value,
          },
          router
        );
        return !!result;
      } else {
        toast.error("Please fix the validation errors");
        return false;
      }
    } catch (err: any) {
      console.error("Handle submit error:", err);
      toast.error("Something went wrong" + (err.message || ""));
      return false;
    } finally {
      isLoading.value = false;
    }
  };

  const loginUser = async (userData: UserSignup, router: any) => {
    const toast = useToast();
    isLoading.value = true;
    try {
      const postResponse = await authuser.login(userData);
      if (postResponse) {
        currentUser.value = postResponse;
        toast.success("Login successful!");
        email.value = "";
        password.value = "";
        Object.keys(error).forEach((key) => ((error as any)[key] = ""));
        if (router) router.replace("/dashboard");
        return postResponse;
      }
    } catch (err: any) {
      console.error("Error logging in:", err);
      toast.error("Login failed" + " " + (err.message || ""));
      return false;
    } finally {
      isLoading.value = false;
    }
  };

  const handleLogin = async (router: any) => {
    const toast = useToast();
    isLoading.value = true;
    try {
      validateEmail();
      validatePassword();
      if (!error.email && !error.password) {
        const result = await loginUser(
          { email: email.value, password: password.value },
          router
        );
        return !!result;
      } else {
        toast.error("Please fix the validation errors");
        return false;
      }
    } catch (err: any) {
      console.error("Handle login error:", err);
      toast.error("Something went wrong" + (err.message || ""));
      return false;
    } finally {
      isLoading.value = false;
    }
  };

  const logout = async (router: any) => {
    const toast = useToast();

    const result = await Swal.fire({
      title: "Are you sure?",
      text: "You’ll be logged out of your account.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, log me out",
      cancelButtonText: "Cancel",
    });

    if (!result.isConfirmed) return false;

    isLoading.value = true;
    try {
      const response = await authuser.logout();
      if (response) {
        toast.success("You have been logged out successfully!");
        currentUser.value = null;
        if (router) router.replace("/login");
        return response;
      }
    } catch (err: any) {
      console.error("logout failed", err);
      // logout does not need to be block even it there is an error it must still work

      // await Swal.fire({
      //   icon: "error",
      //   title: "Logout failed",
      //   text: "Please try again later.",
      //   confirmButtonText: "OK",
      // });
      return false;
    } finally {
      currentUser.value = null;
      toast.success("You have been logged out successfully!");
      if (router) router.replace("/login");
      isLoading.value = false;
    }
  };

  const requestPasswordReset = async (emailParam: string) => {
    const toast = useToast();
    isLoading.value = true;
    try {
      await authuser.forgotPassword({ email: emailParam });
      toast.success("Password reset link sent to email.");
      return true;
    } catch (err: any) {
      toast.error("Unable to send reset link. " + (err.message || ""));
      return false;
    } finally {
      isLoading.value = false;
    }
  };

  const performPasswordReset = async (payload: {
    token: string;
    email: string;
    password: string;
    password_confirmation: string;
  }) => {
    const toast = useToast();
    isLoading.value = true;
    try {
      await authuser.resetPassword(payload);
      toast.success("Password has been reset. You can log in now.");
      return true;
    } catch (err: any) {
      toast.error("Reset failed. " + (err.message || ""));
      return false;
    } finally {
      isLoading.value = false;
    }
  };

  const changePassword = async (payload: {
    current_password: string;
    password: string;
    password_confirmation: string;
  }) => {
    const toast = useToast();
    isLoading.value = true;
    try {
      await authuser.updatePassword(payload);
      toast.success("Password updated successfully.");
      return true;
    } catch (err: any) {
      toast.error(err?.message || "Unable to update password");
      return false;
    } finally {
      isLoading.value = false;
    }
  };

  const changeProfileInformation = async (payload: UserSignup) => {
    const toast = useToast();
    isLoading.value = true;
    try {
      await authuser.updateProfileInfor(payload);
      toast.success("Profile updated successfully.");
      return true;
    } catch (err: any) {
      toast.error(err?.message || "Unable to update profile");
      return false;
    } finally {
      isLoading.value = false;
    }
  };

  // ===== Return all state, getters, actions =====
  return {
    // state
    name,
    email,
    password,
    confirmPassword,
    isLoading,
    resend,
    timezone,
    currentUser,
    error,

    // getters
    isPasswordMatching,
    isAuthenticated,
    isVerified,
    needsVerification,
    canResend,
    activeTimezone,

    // actions
    validateName,
    validateEmail,
    validatePassword,
    validateConfirmPassword,
    fetchCurrentUser,
    resendVerification,
    setTimezone,
    formatTs,
    // createUser,
    handleSubmit,
    // loginUser,
    handleLogin,
    logout,
    requestPasswordReset,
    performPasswordReset,
    changePassword,
    changeProfileInformation,
  };
});
