import { API_BASE } from './api.js';

export function getProfilePictureUrl(path) {
  if (!path) return null;
  if (/^https?:\/\//i.test(path)) return path;
  const trimmed = path.replace(/^\/+/, '');
  return `${API_BASE}/api/storage/${trimmed}`;
}

export function getProfilePictureUrlWithCacheBuster(path) {
  const url = getProfilePictureUrl(path);
  if (!url) return null;
  const separator = url.includes('?') ? '&' : '?';
  return `${url}${separator}t=${Date.now()}`;
}
