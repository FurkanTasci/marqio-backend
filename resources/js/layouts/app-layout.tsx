import { Head, Link } from '@inertiajs/react';
import { Home, Bookmark, Rss, Settings, LogOut } from 'lucide-react';
import { router } from '@inertiajs/react';

const SidebarItem = ({ icon: Icon, label, href, active }) => (
    <Link
        href={href}
        className={`flex items-center gap-3 px-4 py-3 rounded-xl cursor-pointer transition font-medium ${
            active ? "bg-black text-white" : "text-gray-700 hover:bg-gray-100"
        }`}
    >
        <Icon size={20} />
        <span>{label}</span>
    </Link>
);

export default function AppLayout({ children }) {
    const currentPath = window.location.pathname;

    // Logout-Funktion
    const handleLogout = () => {
        router.post('/logout'); // Hier kannst du die Logout-Route aufrufen
    };

    return (
        <>
            <Head title="Marqio" />
            <div className="flex h-screen w-screen max-w-[1200px] overflow-hidden m-auto">
                {/* Linke Sidebar (fixiert) */}
                <aside className="w-64 border-r border-gray-200 p-4 hidden md:flex flex-col h-full fixed">
                    <h1 className="text-xl font-semibold mb-6 text-gray-800 tracking-wide">
                        Marqio
                    </h1>
                    <div className="flex flex-col gap-2">
                        <SidebarItem icon={Home} label="Home" href="/dashboard" active={currentPath === '/dashboard'} />
                        <SidebarItem icon={Rss} label="RSS Sources" href="/rss-sources" active={currentPath === '/rss-sources'} />
                        <SidebarItem icon={Bookmark} label="Bookmarks" href="/bookmarks" active={currentPath === '/bookmarks'} />
                        <SidebarItem icon={Settings} label="Settings" href="/settings" active={currentPath === '/settings'} />
                        
                        {/* Einfacher Logout-Button */}
                        <button
                            onClick={handleLogout}
                            className="flex items-center gap-3 px-4 py-3 mt-auto rounded-xl cursor-pointer transition font-medium text-gray-700 hover:bg-gray-100"
                        >
                            <LogOut size={20} />
                            <span>Logout</span>
                        </button>
                    </div>
                </aside>

                {/* Hauptinhalt (mit Offset für Sidebar) */}
                <div className="md:ml-64 flex-1 flex justify-center h-full overflow-hidden">
                    {children}
                </div>
            </div>
        </>
    );
}