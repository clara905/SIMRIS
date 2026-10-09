import axios from 'axios';
import { useAuth } from '@/stores/auth';

export const USE_MOCK = import.meta.env.VITE_USE_MOCK === 'true';

export const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  withCredentials: true, // cookie sesi Sanctum SPA
  withXSRFToken: true,
  headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
});

api.interceptors.response.use(
  (res) => res,
  (error: unknown) => {
    if (axios.isAxiosError(error) && error.response?.status === 401) {
      useAuth.getState().setUser(null); // ProtectedRoute yang mengarahkan ke /login
    }
    return Promise.reject(error);
  },
);

export function getErrorMessage(err: unknown, fallback = 'Terjadi kesalahan'): string {
  if (typeof err === 'object' && err !== null && 'response' in err) {
    const data = (err as { response?: { data?: { message?: string } } }).response?.data;
    if (data?.message) return data.message;
  }
  return fallback;
}