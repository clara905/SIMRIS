import { useCallback, useEffect, useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Loader2, RefreshCw, ShieldCheck } from 'lucide-react';
import { useLocation, useNavigate } from 'react-router-dom';
import { toast } from 'sonner';
import { fetchCaptcha, login } from '@/api/auth';
import { getErrorMessage, USE_MOCK } from '@/api/client';
import { FormField } from '@/components/shared/FormField';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useAuth } from '@/stores/auth';
import type { Captcha } from '@/types';

const schema = z.object({
  username: z.string().trim().min(1, 'Username wajib diisi'),
  password: z.string().min(1, 'Password wajib diisi'),
  captcha: z.string().trim().min(1, 'Captcha wajib diisi'),
});
type FormValues = z.infer<typeof schema>;

export default function LoginPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const setUser = useAuth((s) => s.setUser);
  const from = (location.state as { from?: { pathname: string } } | null)?.from?.pathname ?? '/';

  const [captcha, setCaptcha] = useState<Captcha | null>(null);
  const [loadingCaptcha, setLoadingCaptcha] = useState(false);

  const {
    register,
    handleSubmit,
    setValue,
    formState: { errors, isSubmitting },
  } = useForm<FormValues>({
    resolver: zodResolver(schema),
    defaultValues: { username: '', password: '', captcha: '' },
  });

  const loadCaptcha = useCallback(async () => {
    setLoadingCaptcha(true);
    try {
      setCaptcha(await fetchCaptcha());
      setValue('captcha', '');
    } catch {
      toast.error('Gagal memuat captcha. Coba muat ulang.');
    } finally {
      setLoadingCaptcha(false);
    }
  }, [setValue]);

  useEffect(() => {
    void loadCaptcha();
  }, [loadCaptcha]);

  async function onSubmit(values: FormValues) {
    if (!captcha) {
      toast.error('Captcha belum siap.');
      return;
    }
    try {
      const user = await login({ ...values, captcha_key: captcha.key });
      setUser(user);
      navigate(from, { replace: true });
    } catch (err) {
      toast.error(getErrorMessage(err, 'Login gagal. Periksa data kamu.'));
      void loadCaptcha(); // captcha sekali pakai
    }
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-muted/40 p-4">
      <Card className="w-full max-w-md">
        <CardHeader className="space-y-2 text-center">
          <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-primary text-primary-foreground">
            <ShieldCheck className="h-6 w-6" />
          </div>
          <CardTitle className="text-2xl">SIMRIS</CardTitle>
          <CardDescription>Sistem Informasi Manajemen Risiko</CardDescription>
        </CardHeader>
        <CardContent>
          <form onSubmit={handleSubmit(onSubmit)} className="space-y-4" noValidate>
            <FormField label="Username" htmlFor="username" error={errors.username?.message}>
              <Input id="username" autoComplete="username" autoFocus {...register('username')} />
            </FormField>

            <FormField label="Password" htmlFor="password" error={errors.password?.message}>
              <Input id="password" type="password" autoComplete="current-password" {...register('password')} />
            </FormField>

            <FormField label="Captcha" htmlFor="captcha" error={errors.captcha?.message}>
              <div className="flex items-center gap-2">
                <div className="flex h-12 w-40 shrink-0 items-center justify-center overflow-hidden rounded-md border bg-white">
                  {captcha ? (
                    <img src={captcha.image} alt="Kode captcha" className="h-full w-full object-contain" />
                  ) : (
                    <Loader2 className="h-4 w-4 animate-spin text-muted-foreground" />
                  )}
                </div>
                <Button
                  type="button"
                  variant="outline"
                  size="icon"
                  onClick={() => void loadCaptcha()}
                  disabled={loadingCaptcha}
                  aria-label="Muat ulang captcha"
                >
                  <RefreshCw className={loadingCaptcha ? 'h-4 w-4 animate-spin' : 'h-4 w-4'} />
                </Button>
              </div>
              <Input id="captcha" autoComplete="off" placeholder="Ketik kode di atas" {...register('captcha')} />
            </FormField>

            <Button type="submit" className="w-full" disabled={isSubmitting || !captcha}>
              {isSubmitting && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
              Masuk
            </Button>

            {USE_MOCK && (
              <p className="rounded-md bg-amber-50 p-2 text-xs text-amber-800">
                Mode mock: isi username dengan nama role (super_admin, admin_satker, kasatker,
                kabid, kasubbid, staf), password bebas.
              </p>
            )}
          </form>
        </CardContent>
      </Card>
    </div>
  );
}