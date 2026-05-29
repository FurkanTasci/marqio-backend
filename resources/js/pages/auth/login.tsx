import { Form, Head, Link } from '@inertiajs/react'
import {
    Rss,
    Mail,
    Lock,
    ArrowRight,
    LogIn,
} from 'lucide-react'

import InputError from '@/components/input-error'
import { Checkbox } from '@/components/ui/checkbox'
import { Spinner } from '@/components/ui/spinner'
import { register } from '@/routes'
import { store } from '@/routes/login'
import { request } from '@/routes/password'

interface LoginProps {
    status?: string
    canResetPassword: boolean
    canRegister: boolean
}

export default function Login({
    status,
    canResetPassword,
    canRegister,
}: LoginProps) {
    return (
        <>
            <Head title="Login – Marqio" />

            <div className="min-h-screen bg-zinc-50 dark:bg-zinc-950 flex flex-col">

                {/* Top Brand */}
                <div className="pt-10 text-center">
                    <Link href="/" className="inline-flex items-center gap-3">
                        <div className="p-2 bg-black text-white rounded-xl dark:bg-white dark:text-black">
                            <Rss size={18} />
                        </div>
                        <span className="font-semibold text-lg tracking-tight text-zinc-800 dark:text-white">
                            Marqio
                        </span>
                    </Link>
                </div>

                {/* Center Card */}
                <div className="flex-1 flex items-center justify-center px-6 py-12">

                    <div className="w-full max-w-md bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl shadow-sm p-8 space-y-8">

                        <div className="text-center space-y-2">
                            <h1 className="text-2xl font-semibold tracking-tight">
                                Welcome back
                            </h1>
                            <p className="text-sm text-zinc-600 dark:text-zinc-400">
                                Log in to access your feeds and bookmarks
                            </p>
                        </div>

                        <Form
                            {...store.form()}
                            resetOnSuccess={['password']}
                            className="space-y-6"
                        >
                            {({ processing, errors }) => (
                                <>
                                    {/* Email */}
                                    <div className="space-y-2">
                                        <label className="text-sm font-medium">
                                            Email
                                        </label>
                                        <div className="relative">
                                            <Mail
                                                size={16}
                                                className="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400"
                                            />
                                            <input
                                                type="email"
                                                name="email"
                                                required
                                                autoFocus
                                                autoComplete="email"
                                                placeholder="email@example.com"
                                                className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-zinc-300 dark:border-zinc-700 bg-transparent focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-white transition"
                                            />
                                        </div>
                                        <InputError message={errors.email} />
                                    </div>

                                    {/* Password */}
                                    <div className="space-y-2">
                                        <div className="flex justify-between items-center">
                                            <label className="text-sm font-medium">
                                                Password
                                            </label>

                                            {canResetPassword && (
                                                <Link
                                                    href={request()}
                                                    className="text-xs text-zinc-500 hover:text-zinc-800 dark:hover:text-white"
                                                >
                                                    Forgot?
                                                </Link>
                                            )}
                                        </div>

                                        <div className="relative">
                                            <Lock
                                                size={16}
                                                className="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400"
                                            />
                                            <input
                                                type="password"
                                                name="password"
                                                required
                                                autoComplete="current-password"
                                                placeholder="Password"
                                                className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-zinc-300 dark:border-zinc-700 bg-transparent focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-white transition"
                                            />
                                        </div>
                                        <InputError message={errors.password} />
                                    </div>

                                    {/* Remember */}
                                    <div className="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                                        <Checkbox id="remember" name="remember" />
                                        <label htmlFor="remember">
                                            Remember me
                                        </label>
                                    </div>

                                    {/* Submit */}
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl bg-black text-white hover:opacity-90 transition disabled:opacity-70 dark:bg-white dark:text-black"
                                    >
                                        {processing ? (
                                            <Spinner />
                                        ) : (
                                            <>
                                                Log in
                                                <ArrowRight size={16} />
                                            </>
                                        )}
                                    </button>
                                </>
                            )}
                        </Form>

                        {canRegister && (
                            <div className="text-center text-sm text-zinc-600 dark:text-zinc-400">
                                Don’t have an account?{' '}
                                <Link
                                    href={register()}
                                    className="font-medium text-black dark:text-white hover:underline"
                                >
                                    Create one
                                </Link>
                            </div>
                        )}

                        {status && (
                            <div className="text-center text-sm font-medium text-green-600">
                                {status}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </>
    )
}