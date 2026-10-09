import type { DashboardSummary, StafDashboardData } from '@/types';
import { api, USE_MOCK } from './client';
import { mockStaf, mockSummary, wait } from './mock';

export const dashboardApi = {
  async summary(): Promise<DashboardSummary> {
    if (USE_MOCK) {
      await wait();
      return mockSummary;
    }
    const { data } = await api.get<{ data: DashboardSummary }>('/api/dashboard/summary');
    return data.data;
  },
  async staf(): Promise<StafDashboardData> {
    if (USE_MOCK) {
      await wait();
      return mockStaf;
    }
    const { data } = await api.get<{ data: StafDashboardData }>('/api/dashboard/staf');
    return data.data;
  },
};