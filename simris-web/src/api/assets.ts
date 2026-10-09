import type { Asset, AssetCategory, AssetPayload } from '@/types';
import { api, USE_MOCK } from './client';
import { mockAssets, mockCategories, wait } from './mock';

export const assetApi = {
  async list(): Promise<Asset[]> {
    if (USE_MOCK) {
      await wait();
      return mockAssets.list();
    }
    const { data } = await api.get<{ data: Asset[] }>('/api/assets');
    return data.data;
  },
  async categories(): Promise<AssetCategory[]> {
    if (USE_MOCK) return mockCategories;
    const { data } = await api.get<{ data: AssetCategory[] }>('/api/asset-categories');
    return data.data;
  },
  async create(payload: AssetPayload): Promise<Asset> {
    if (USE_MOCK) {
      await wait();
      return mockAssets.create(payload);
    }
    const { data } = await api.post<{ data: Asset }>('/api/assets', payload);
    return data.data;
  },
  async update(id: number, payload: AssetPayload): Promise<Asset> {
    if (USE_MOCK) {
      await wait();
      return mockAssets.update(id, payload);
    }
    const { data } = await api.put<{ data: Asset }>(`/api/assets/${id}`, payload);
    return data.data;
  },
  async remove(id: number): Promise<void> {
    if (USE_MOCK) {
      await wait();
      mockAssets.remove(id);
      return;
    }
    await api.delete(`/api/assets/${id}`);
  },
};