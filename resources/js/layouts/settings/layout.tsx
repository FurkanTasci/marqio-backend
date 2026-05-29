import { Link } from '@inertiajs/react';
import { type PropsWithChildren } from 'react';
import { Home, Bookmark, Rss, Settings, User, Key, Paintbrush } from 'lucide-react';
import { useActiveUrl } from '@/hooks/use-active-url';
import { cn } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit } from '@/routes/profile';
import { show } from '@/routes/two-factor';
import { edit as editPassword } from '@/routes/user-password';
import { type NavItem } from '@/types';



// --- SettingsNavItem (für die Settings-Subnavigation) ---
const SettingsNavItem = ({ title, href, active, icon: Icon }) => (
    <Link
        href={href}
        className={`flex items-center gap-3 px-4 py-3 rounded-lg transition-all ${
            active ? "bg-gray-100 text-black font-medium" : "text-gray-600 hover:bg-gray-50"
        }`}
    >
        {Icon && <Icon size={18} />}
        <span>{title}</span>
    </Link>
);

export default function SettingsLayout({ children }: PropsWithChildren) {
    const { urlIsActive } = useActiveUrl();
    const currentPath = window.location.pathname;

    // Navigationsitems für die Settings-Subnavigation
    const settingsNavItems: NavItem[] = [
        { title: 'Profile', href: edit(), icon: User },
        { title: 'Password', href: editPassword(), icon: Key },
        { title: 'Two-Factor Auth', href: show(), icon: null },
        { title: 'Appearance', href: editAppearance(), icon: Paintbrush },
    ];

    // Wenn server-seitig gerendert, nur client-seitig anzeigen
    if (typeof window === 'undefined') {
        return null;
    }

    return (
        <div className="flex h-screen w-screen overflow-hidden">

            {/* Hauptinhalt (mit Offset für die Sidebar) */}
            <div className="flex-1 flex justify-center h-full overflow-hidden">
                <div className="flex w-full h-full">
                    {/* Settings-Inhalt mit Subnavigation */}
                    <main className="flex-1 flex flex-col border-r border-gray-200 h-full">
                        {/* Fixierter Header */}
                        <div className="h-16 flex items-center px-6 border-b border-gray-200 sticky top-0 bg-white z-10">
                            <h2 className="font-semibold text-lg">Settings</h2>
                        </div>

                        {/* Hauptinhalt mit Subnavigation und Children */}
                        <div className="flex-1 overflow-y-auto p-6">
                            <div className="flex flex-col lg:flex-row gap-8">
                                {/* Subnavigation für Settings */}
                                <aside className="w-full lg:w-64 flex-shrink-0">
                                    <nav className="bg-gray-50 rounded-lg p-4 space-y-1">
                                        <h3 className="text-sm font-semibold text-gray-500 mb-3 px-1">Settings</h3>
                                        {settingsNavItems.map((item, index) => (
                                            <SettingsNavItem
                                                key={`settings-nav-${index}`}
                                                title={item.title}
                                                href={item.href}
                                                active={urlIsActive(item.href)}
                                                icon={item.icon}
                                            />
                                        ))}
                                    </nav>
                                </aside>

                                {/* Kinder-Inhalt (z. B. Profile-Formular) */}
                                <div className="flex-1 max-w-2xl">
                                    {children}
                                </div>
                            </div>
                        </div>
                    </main>
                </div>
            </div>
        </div>
    );
}
