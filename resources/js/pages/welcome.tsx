import { Head, Link, usePage } from '@inertiajs/react'
import {
    Rss,
    Bookmark,
    Folder,
    Search,
    Sparkles,
    ArrowRight,
    LogIn,
    UserPlus
} from 'lucide-react'

import { login, register } from '@/routes'
import { type SharedData } from '@/types'

export default function Welcome({
    canRegister = true,
}: {
    canRegister?: boolean
}) {
    const { auth } = usePage<SharedData>().props

    return (
        <>
            <Head title="Marqio – RSS & Bookmarks" />

            <div className="min-h-screen bg-zinc-50 dark:bg-zinc-950 text-zinc-800 dark:text-zinc-100 flex flex-col">

                {/* Navigation */}
                <header className="w-full border-b border-zinc-200 dark:border-zinc-800">
                    <div className="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">

                        <div className="flex items-center gap-3">
                            <div className="p-2 bg-black text-white rounded-xl dark:bg-white dark:text-black">
                                <Rss size={18} />
                            </div>
                            <span className="font-semibold text-lg tracking-tight">
                                Marqio
                            </span>
                        </div>

                        <nav className="flex items-center gap-3">
                            {auth.user ? (
                                <Link
                                    href="/dashboard"
                                    className="flex items-center gap-2 px-4 py-2 rounded-xl bg-black text-white hover:opacity-90 dark:bg-white dark:text-black"
                                >
                                    Dashboard
                                    <ArrowRight size={16} />
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={login()}
                                        className="flex items-center gap-2 px-4 py-2 rounded-xl border border-zinc-300 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-900"
                                    >
                                        <LogIn size={16} />
                                        Login
                                    </Link>

                                    {canRegister && (
                                        <Link
                                            href={register()}
                                            className="flex items-center gap-2 px-4 py-2 rounded-xl bg-black text-white hover:opacity-90 dark:bg-white dark:text-black"
                                        >
                                            <UserPlus size={16} />
                                            Register
                                        </Link>
                                    )}
                                </>
                            )}
                        </nav>
                    </div>
                </header>

                {/* Hero */}
                <main className="flex-1 flex items-center">
                    <div className="max-w-6xl mx-auto px-6 py-20 grid lg:grid-cols-2 gap-16 items-center">

                        {/* Left */}
                        <div>
                            <div className="inline-flex items-center gap-2 text-sm px-3 py-1 rounded-full bg-zinc-200 dark:bg-zinc-800 mb-6">
                                <Sparkles size={14} />
                                Your personal knowledge hub
                            </div>

                            <h1 className="text-4xl lg:text-5xl font-bold leading-tight tracking-tight mb-6">
                                All your feeds.
                                <br />
                                All your bookmarks.
                                <br />
                                <span className="text-zinc-400">
                                    One clean place.
                                </span>
                            </h1>

                            <p className="text-lg text-zinc-600 dark:text-zinc-400 mb-8">
                                Follow RSS feeds, save articles, organize links,
                                and build your personal reading system.
                                Minimal. Fast. Focused.
                            </p>

                            <div className="flex gap-4">
                                <Link
                                    href={auth.user ? '/dashboard' : register()}
                                    className="flex items-center gap-2 px-6 py-3 rounded-xl bg-black text-white hover:opacity-90 dark:bg-white dark:text-black"
                                >
                                    Get Started
                                    <ArrowRight size={18} />
                                </Link>

    
                            </div>
                        </div>

                        {/* Right Feature Card */}
                        <div className="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-8 shadow-sm space-y-6">

                            <Feature
                                icon={<Rss size={18} />}
                                title="RSS Reader"
                                description="Subscribe to your favorite blogs, newsletters and news sources."
                            />

                            <Feature
                                icon={<Bookmark size={18} />}
                                title="Smart Bookmarks"
                                description="Save articles and links with tags and folders."
                            />

                            <Feature
                                icon={<Folder size={18} />}
                                title="Organize"
                                description="Group feeds and bookmarks into collections."
                            />

                            <Feature
                                icon={<Search size={18} />}
                                title="Instant Search"
                                description="Find any article or link in seconds."
                            />
                        </div>
                    </div>
                </main>

                {/* Footer */}
                <footer className="border-t border-zinc-200 dark:border-zinc-800 py-6 text-center text-sm text-zinc-500">
                    © {new Date().getFullYear()} Marqio — RSS & Bookmark Manager
                </footer>
            </div>
        </>
    )
}

function Feature({
    icon,
    title,
    description,
}: {
    icon: React.ReactNode
    title: string
    description: string
}) {
    return (
        <div className="flex items-start gap-4">
            <div className="p-2 rounded-xl bg-zinc-100 dark:bg-zinc-800">
                {icon}
            </div>
            <div>
                <h3 className="font-medium mb-1">{title}</h3>
                <p className="text-sm text-zinc-600 dark:text-zinc-400">
                    {description}
                </p>
            </div>
        </div>
    )
}