import axios from "axios";
import type UserSignup from "@/Typescript/UserSignup";
import type Payment from "@/Typescript/Payment";

const API_BASE_URL = "http://localhost:8000/api";
const API_ROOT = "http://localhost:8000"; // root without /api

// Create the axios instance
const api = axios.create({
  baseURL: API_BASE_URL,
  withCredentials: true, // allow cookies for Sanctum
});

const rootApi = axios.create({
  baseURL: API_ROOT,
  withCredentials: true,
});

// You only need await initializeCsrf() for browser-based,
// cookie-authenticated requests that send data
// (POST, PUT, PATCH, DELETE).

export const initializeCsrf = async () => {
  try {
    await rootApi.get("/sanctum/csrf-cookie");
  } catch (error) {
    console.error("Failed to initialize CSRF token:", error);
  }
};

// Request interceptor to set CSRF token automatically
// Reusable function to attach X-XSRF-TOKEN from cookie
function attachXsrfHeader(config: any) {
  const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
  if (match) config.headers["X-XSRF-TOKEN"] = decodeURIComponent(match[1]);
  return config;
}

// Add request interceptors to BOTH instances

// for the api route
api.interceptors.request.use(attachXsrfHeader);
// for the web route
rootApi.interceptors.request.use(attachXsrfHeader);

// Optional: Response interceptor for logging or error handling
// ok so here the error thrown here is the error from the axios that is from the backend that the response and error which is like the .result and .error in promise will be the error catch in the signup and others
api.interceptors.response.use(
  (response) => response,
  (error) => {
    console.error("API error:", error.response?.data || error.message);
    return Promise.reject(error);
  }
);

rootApi.interceptors.response.use(
  (response) => response,
  (error) => {
    console.error("API error:", error.response?.data || error.message);
    return Promise.reject(error);
  }
);

// auth services
export const authuser = {
  async signup(userData: UserSignup) {
    try {
      await initializeCsrf(); // Get CSRF token first

      const { data } = await rootApi.post("/register", userData);
      return data.user;
      // if (status === 200 || status === 201) {
      //   return data.user;
      // } else {
      //   // well this error here is thrown if after the backend response successful and the status is not what is expexted like its 500 instead of 200
      //   throw new Error(data?.message || "Signup failed");
      // }
    } catch (error: any) {
      throw new Error(error?.response?.data?.message || "Signup failed");
    }
  },

  async login(userData: UserSignup) {
    try {
      await initializeCsrf(); // Get CSRF token first

      const { data } = await rootApi.post("/login", userData);
      return data.user;
      // if (status === 200) {
      //   return data.user;
      // } else {
      //   throw new Error(data?.message || "Signup failed");
      // }
    } catch (error: any) {
      throw new Error(
        error?.response?.data?.message || error?.message || "Login failed"
      );
    }
  },

  async logout() {
    try {
      const { data } = await rootApi.post("/logout");
      return data?.message || "Logged out successfully";
      // if (status === 200) {
      //   return data?.message || "Logged out successfully";
      // }
    } catch (error: any) {
      // logout does not need to be block even it there is an error it must still work
      throw new Error(
        error?.response?.data?.message || error?.message || "Logout failed"
      );
    }
  },

  async me() {
    const { data } = await api.get("/user");
    return data;
  },

  async resendVerification() {
    return rootApi.post("/email/verification-notification");
  },

  async forgotPassword(email: any) {
    await initializeCsrf(); // Get CSRF token first
    // with destructuring i will get access to the data straight like the status data header instead of it all and awaiting response.data
    const { data } = await rootApi.post("/forgot-password", email);
    return data;
  },

  async resetPassword(payload: any) {
    // payload: { token, email, password, password_confirmation }
    await initializeCsrf(); // Get CSRF token first
    const { data } = await rootApi.post("/reset-password", payload);
    return data;
  },
  async updatePassword(payload: any) {
    // payload = { current_password, password, password_confirmation }
    await initializeCsrf();
    const { data } = await rootApi.put("/user/password", payload);
    return data;
  },

  async updateProfileInfor(payload: any) {
    // payload ={email, name}
    await initializeCsrf();
    const { data } = await rootApi.put("user/profile-information", payload);
    return data;
  },
};

// payment services
export const paymentService = {
  async initializePayment(payload: Payment) {
    try {
      // payload (reduced): { plan_id, customer_email, customer_name }
      const { data } = await rootApi.post("/pay", payload);
      return data;
    } catch (error: any) {
      console.error("Payment initialization error:", error);
      throw new Error(
        error?.response?.data?.message || "Payment initialization failed"
      );
    }
  },

  async verifyPayment(transactionId: string) {
    try {
      const { data } = await rootApi.get(
        `/callback?transaction_id=${transactionId}`
      );
      return data;
    } catch (error: any) {
      console.error("Payment verification error:", error);
      throw new Error(
        error?.response?.data?.message || "Payment verification failed"
      );
    }
  },

  async transactionDetails(transactionId: string) {
    try {
      const { data } = await rootApi.get(
        `/transaction?transaction_id=${transactionId}`
      );
      return data;
    } catch (error: any) {
      console.error("Fetching details failed:", error);
      throw new Error(
        error?.response?.data?.message || "No Payment details data"
      );
    }
  },
};
