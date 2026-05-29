import { Bookmark, ExternalLink, Rss, Search, MoreHorizontal } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { usePage } from '@inertiajs/react';
import AppTags from '@/components/app-tags';

const BookmarksCard = () => {
    const { bookmarks } = usePage().props;
    console.log(bookmarks);
    return (
        <div className="space-y-6">
            {bookmarks.map((bookmark) => (
                <div key={bookmark.id} className="border-b border-gray-200 p-6 hover:bg-gray-50 transition cursor-pointer">
                    <div className="flex gap-4">
                        <div className="w-12 h-12 rounded-full bg-gray-300 flex-shrink-0">
                            {bookmarks.url && (
                                <img 
                                    src={`https://www.google.com/s2/favicons?domain=${new URL(bookmarks.url).hostname}`} 
                                    alt="Favicon" 
                                    className="w-full h-full rounded-full object-cover" 
                                />
                            )}
                        </div>
                        <div className="flex-1">
                            <div className="flex justify-between items-center">
                                <h3 className="font-semibold text-gray-900">{bookmark.title.length > 30 ? bookmark.title.slice(0, 30) + "..." : bookmark.title}</h3>
                                {/* Datum und Zeit formatieren */}
                                <span className="text-sm text-gray-400">
                                    {new Date(bookmark.created_at).toLocaleString()}
                                </span>
                            </div>
                            <p className="text-gray-600 text-sm mt-2">
                                {/* Fallback für Beschreibung */}
                                {bookmark.description || "Keine Beschreibung verfügbar"}
                            </p>
                            {bookmark.tags?.length > 0 && (
                                <div className="flex flex-wrap gap-2 mt-3">
                                    {bookmark.tags.map((tag) => (
                                        <span
                                            key={tag.id}
                                            className="px-3 py-1 text-xs bg-gray-200 text-gray-700 rounded-full"
                                        >
                                            #{tag.name}
                                        </span>
                                    ))}
                                </div>
                            )}
                            {bookmark.image && (
                                <div className="mt-4 rounded-2xl overflow-hidden">
                                    <img src={bookmark.image} alt="Post" className="w-full h-full object-cover" />
                                </div>
                            )}
                      

                            <div className="flex gap-6 mt-4 text-gray-500 text-sm">
                                <button className="flex items-center gap-1 hover:text-black transition">
                                    <Bookmark size={16} /> Save
                                </button>

                                <a 
                                    href={bookmark.url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="flex items-center gap-1 hover:text-black transition"
                                >
                                    <ExternalLink size={16} /> Open
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            ))}
        </div>
    );
};

export default function Bookmarks() {
    return (
        <AppLayout>
            <div className="flex w-full max-w-[1200px] h-full">
                {/* Feed-Bereich */}
                <main className="flex-1 flex flex-col border-r border-gray-200 h-full">
                    {/* Fixierter Header */}
                    <div className="h-16 flex items-center justify-between px-6 border-b border-gray-200 sticky top-0 bg-white z-10">
                        <h2 className="font-semibold text-lg">Bookmarks</h2>
                        <Search size={20} className="text-gray-500 cursor-pointer" />
                    </div>
                    {/* Scrollbarer Feed-Inhalt */}
                    <div className="flex-1 overflow-y-auto">
                        <BookmarksCard />
                    </div>
                </main>
                <AppTags />
            </div>
        </AppLayout>
    );
}