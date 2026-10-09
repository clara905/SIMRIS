import type { Captcha, LoginPayload, User } from '@/types';
import { api, USE_MOCK } from './client';
import { mockCaptcha, mockError, mockLogin, mockSession, wait } from './mock';

export async function fetchCaptcha(): Promise<Captcha> {
  if (USE_MOCK) {
    await wait(150);
    return mockCaptcha();
  }
  const { data } = await api.get<{ data: Captcha }>('/api/captcha');
  return data.data;
}

export async function login(payload: LoginPayload): Promise<User> {
  if (USE_MOCK) {
    await wait();
    return mockLogin(payload);
  }
  await api.get('/sanctum/csrf-cookie');
  const { data } = await api.post<{ data: User }>('/api/login', payload);
  return data.data;
}

export async function fetchMe(): Promise<User> {
  if (USE_MOCK) {
    const u = mockSession.get();
    if (!u) throw mockError(401, 'Unauthenticated');
    return u;
  }
  const { data } = await api.get<{ data: User }>('/api/me');
  return data.data;
}

export async function logout(): Promise<void> {
  if (USE_MOCK) {
    mockSession.clear();
    return;
  }
  await api.post('/api/logout');
}