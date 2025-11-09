import axios from "axios";

const API_BASE_URL = "http://localhost:8000/api";
const API_ROOT = "http://localhost:8000"; // root without /api

// Create the axios instance
const api = axios.create({
  baseURL: API_BASE_URL,
  withCredentials: true, // allow cookies for Sanctum
  headers: {
    "Content-Type": "application/json",
  },
});

const rootApi = axios.create({
  baseURL: API_ROOT,
  withCredentials: true,
});

export const initializeCsrf = async () => {
  try {
    await rootApi.get("/sanctum/csrf-cookie");
  } catch (error) {
    console.error("Failed to initialize CSRF token:", error);
  }
};

// Request interceptor to set CSRF token automatically
// Reusable function to attach X-XSRF-TOKEN from cookie
function attachXsrfHeader(config) {
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
  async signup(userData) {
    try {
      await initializeCsrf(); // Get CSRF token first

      const { data, status } = await rootApi.post("/register", userData);
      if (status === 200 || response.status === 201) {
        return data.user;
      } else {
        // well this error here is thrown if after the backend response successful and the status is not what is expexted like its 500 instead of 200
        throw new Error(data.message);
      }
    } catch (error) {
      console.error("signup err", error);
      throw new Error(error.data?.message || error.message || "Signup failed");
    }
  },

  async login(userData) {
    try {
      await initializeCsrf(); // Get CSRF token first

      const { data, status } = await rootApi.post("/login", userData);
      if (status === 200) {
        return data.user;
      } else {
        throw new Error(data.message);
      }
    } catch (error) {
      console.error("login err", error);
      throw new Error(error.data?.message || error?.message || "Login failed");
    }
  },

  async logout() {
    try {
      const { data, status } = await rootApi.post("/logout");
      if (status === 200) {
        return data?.message ?? "Logged out successfully";
      }
    } catch (error) {
      console.error("logout err", error);
    }
  },

  async me() {
    const { data } = await api.get("/user");
    return data;
  },

  async resendVerification() {
    return rootApi.post("/email/verification-notification");
  },

  async forgotPassword(email) {
    await initializeCsrf(); // Get CSRF token first
    // with destructuring i will get access to the data straight like the status data header instead of it all and awaiting response.data
    const { data } = await rootApi.post("/forgot-password", email);
    return data;
  },

  async resetPassword(payload) {
    // payload: { token, email, password, password_confirmation }
    await initializeCsrf(); // Get CSRF token first
    const { data } = await rootApi.post("/reset-password", payload);
    return data;
  },
  async updatePassword(payload) {
    // payload = { current_password, password, password_confirmation }
    await initializeCsrf();
    const { data } = await rootApi.put("/user/password", payload);
    return data;
  },

  async updateProfileInfor(payload) {
    // payload ={email, name}
    await initializeCsrf();
    const { data } = await rootApi.put("user/profile-information", payload);
    return data;
  },
};
