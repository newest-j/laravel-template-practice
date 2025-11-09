import { defineStore } from "pinia";
import Swal from "sweetalert2";
import { useToast } from "vue-toastification";
import type UserSignup from "@/UserData/UserSignup";
import { authuser } from "@/services/api.js";
import { browserTimeZone, formatTimestamp } from "@/utils/Datetime";

export const userAuthStore = defineStore("authstore", {
  state: () => ({
    name: "",
    email: "",
    password: "",
    confirmPassword: "",
    isLoading: false,
    resend: {
      sending: false,
      lastSentAt: null as number | null,
    },
    timezone: browserTimeZone(), // default to browser TZ

    currentUser: null as null | {
      id: number;
      name: string;
      email: string;
      email_verified_at: string;
    },

    error: {
      name: "",
      email: "",
      password: "",
      confirmPassword: "",
    },
  }),
  getters: {
    isPasswordMatching(state): boolean {
      return state.password === state.confirmPassword;
    },
    isAuthenticated(state): boolean {
      // the !! is to make the value return a Boolean
      return !!state.currentUser;
    },
    isVerified(state): boolean {
      return !!state.currentUser?.email_verified_at;
    },
    needsVerification(): boolean {
      return this.isAuthenticated && !this.isVerified;
    },
    canResend(): boolean {
      if (!this.needsVerification) return false;
      if (!this.resend.lastSentAt) return true;
      return Date.now() - this.resend.lastSentAt > 60000; //60s throttle UI
    },
    activeTimezone: (s): string => s.timezone || browserTimeZone(),
  },
  actions: {
    validateName() {
      const fullNameRegex: RegExp = /^[A-Za-z]{2,}(?: [A-Za-z]{2,})+$/;
      if (!this.name.trim()) {
        this.error.name = "Name is required";
      } else if (!fullNameRegex.test(this.name.trim())) {
        this.error.name =
          "Please enter your full name (first and last name, letters only)";
      } else {
        this.error.name = "";
      }
    },

    validateEmail() {
      const emailReg: RegExp =
        /^((\+?[0-9\s\-().]{7,})|([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}))$/;

      if (!this.email.trim()) {
        this.error.email = "Email is required";
      } else if (!emailReg.test(this.email.trim())) {
        this.error.email = "Please enter a valid email address";
      } else {
        this.error.email = "";
      }
    },

    validatePassword() {
      const passwordReg: RegExp =
        /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).{8,20}$/;

      if (!this.password.trim()) {
        this.error.password = "Password is required";
      } else if (!passwordReg.test(this.password.trim())) {
        this.error.password =
          "Password min 8 and max 20 character at least one uppercase,lowercase,digit,and special character";
      } else {
        this.error.password = "";
      }
    },

    validateConfirmPassword() {
      if (!this.confirmPassword.trim()) {
        this.error.confirmPassword = "Please confirm your password";
      } else if (!this.isPasswordMatching) {
        this.error.confirmPassword = "The passwords do not match";
      } else {
        this.error.confirmPassword = "";
      }
    },

    async fetchCurrentUser() {
      try {
        const data = await authuser.me();
        this.currentUser = data;
        return data;
      } catch {
        this.currentUser = null;
        return null;
      }
    },
    async resendVerification() {
      if (!this.canResend) return false;
      this.resend.sending = true;
      try {
        await authuser.resendVerification();
        this.resend.lastSentAt = Date.now();
      } finally {
        this.resend.sending = false;
      }
    },
    // markVerified() {
    //   if (this.currentUser)
    //     this.currentUser.email_verified_at = new Date().toISOString();
    // },

    setTimezone(tz: string) {
      this.timezone = tz;
    },

    formatTs(iso?: string | null, opts?: Intl.DateTimeFormatOptions) {
      return formatTimestamp(iso, this.activeTimezone, opts);
    },

    async createUser(userData: UserSignup, router: any) {
      const toast = useToast();
      this.isLoading = true;
      try {
        const postResponse = await authuser.signup(userData);
        if (postResponse) {
          this.currentUser = postResponse;
          toast.success("Account created successfully!");

          //   reset the form
          this.name = "";
          this.password = "";
          this.email = "";
          this.confirmPassword = "";

          // Reset all error messages
          Object.keys(this.error).forEach((key) => {
            (this.error as any)[key] = "";
          });

          if (router) {
            router.replace("/dashboard");
          }

          return postResponse;
        }
      } catch (error: any) {
        console.error("Error creating user:", error);
        toast.error("Failed to create user. " + (error.message || ""));
        return false;
      } finally {
        this.isLoading = false;
      }
    },

    async handleSubmit(router: any) {
      const toast = useToast();
      this.isLoading = true;
      try {
        this.validateName();
        this.validateEmail();
        this.validatePassword();
        this.validateConfirmPassword();

        if (
          !this.error.name &&
          !this.error.email &&
          !this.error.password &&
          !this.error.confirmPassword
        ) {
          const result = await this.createUser(
            {
              name: this.name,
              email: this.email,
              password: this.password,
              password_confirmation: this.confirmPassword,
            },
            router
          );
          return result ? true : false;
        } else {
          // If validation fails, show error
          toast.error("Please fix the validation errors");
          return false;
        }
      } catch (error: any) {
        console.error("Handle submit error:", error);
        toast.error("Something went wrong" + (error.message || ""));
        return false;
      } finally {
        this.isLoading = false;
      }
    },

    async loginUser(userData: UserSignup, router: any) {
      const toast = useToast();
      this.isLoading = true;
      try {
        const postResponse = await authuser.login(userData);
        if (postResponse) {
          this.currentUser = postResponse;
          toast.success("Login successful!");
          //   reset the form
          this.email = "";
          this.password = "";

          // Reset all error messages
          Object.keys(this.error).forEach((key) => {
            (this.error as any)[key] = "";
          });

          if (router) {
            router.replace("/dashboard");
          }

          return postResponse;
        }
      } catch (error: any) {
        console.error("Error logging in:", error);
        toast.error("Login failed" + " " + (error.message || ""));
        return false;
      } finally {
        this.isLoading = false;
      }
    },

    async handleLogin(router: any) {
      const toast = useToast();
      this.isLoading = true;
      try {
        // Validate all fields
        this.validateEmail();
        this.validatePassword();

        if (!this.error.email && !this.error.password) {
          const result = await this.loginUser(
            {
              email: this.email,
              password: this.password,
            },
            router
          );
          return result ? true : false;
        } else {
          // If validation fails, show error
          toast.error("Please fix the validation errors");
          return false;
        }
      } catch (error: any) {
        console.error("Handle login error:", error);
        toast.error("Something went wrong" + (error.message || ""));
        return false;
      } finally {
        this.isLoading = false;
      }
    },

    async logout(router: any) {
      const toast = useToast();

      // Step 1: Ask for confirmation
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

      // Step 2: If user cancels, just exit
      if (!result.isConfirmed) {
        return false;
      }

      this.isLoading = true;
      try {
        const response = await authuser.logout();
        if (response) {
          toast.success("You have been logged out successfully!");

          // Clear client auth state
          this.currentUser = null;

          if (router) router.replace("/login");

          return response;
        }
      } catch (error: any) {
        console.error("logout failed", error);
        //  Use SweetAlert here – because it’s an unexpected failure / a critical error not a
        // light one to use toast

        await Swal.fire({
          icon: "error",
          title: "Logout failed",
          text: "Please try again later.",
          confirmButtonText: "OK",
        });
        return false;
      } finally {
        this.isLoading = false;
      }
    },

    // link sent
    async requestPasswordReset(email: string) {
      const toast = useToast();
      this.isLoading = true;
      try {
        await authuser.forgotPassword({ email });
        toast.success("Password reset link sent to email.");
        return true;
      } catch (error: any) {
        toast.error("Unable to send reset link. " + (error.message || ""));
        return false;
      } finally {
        this.isLoading = false;
      }
    },

    async performPasswordReset(payload: {
      token: string;
      email: string;
      password: string;
      password_confirmation: string;
    }) {
      const toast = useToast();
      this.isLoading = true;
      try {
        await authuser.resetPassword(payload);
        toast.success("Password has been reset. You can log in now.");
        return true;
      } catch (error: any) {
        toast.error("Reset failed. " + (error.message || ""));
        return false;
      } finally {
        this.isLoading = false;
      }
    },

    async changePassword(payload: {
      current_password: string;
      password: string;
      password_confirmation: string;
    }) {
      const toast = useToast();
      this.isLoading = true;
      try {
        await authuser.updatePassword(payload);
        toast.success("Password updated successfully.");
        return true;
      } catch (error: any) {
        toast.error(error?.message || "Unable to update password");
        return false;
      } finally {
        this.isLoading = false;
      }
    },

    async changeprofileinformation(payload: UserSignup) {
      const toast = useToast();
      this.isLoading = true;
      try {
        await authuser.updateProfileInfor(payload);
        toast.success("Profile updated successfully.");
        return true;
      } catch (error: any) {
        toast.error(error?.message || "Unable to update profile");
        return false;
      } finally {
        this.isLoading = false;
      }
    },
  },
});
