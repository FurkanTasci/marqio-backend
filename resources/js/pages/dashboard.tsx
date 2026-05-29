import {
    Bookmark,
    Search,
    ExternalLink,
} from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { usePage } from '@inertiajs/react';
import AppTags from '@/components/app-tags';

const FeedCard = () => {
    const { feeds } = usePage().props;
    // const allFeeds = feeds?.flatMap(feed => feed.items);
    const allFeeds = feeds?.flatMap(feed =>
        feed.items.map(item => ({
            ...item,
            feedUrl: feed.url
        }))
    );
  
    const getHostname = (url) => {
        if (!url) return null;

        try {
            // Falls kein http/https vorhanden → hinzufügen
            const normalized = url.startsWith("http")
                ? url
                : `https://${url}`;

            return new URL(normalized).hostname;
        } catch {
            return null;
        }
};

    console.log(feeds)

    if (!allFeeds || allFeeds.length === 0) return null;
    
    return (
        <div className="space-y-6">
            {allFeeds.map((feed, index) => (

                <div key={index} className="border-b border-gray-200 p-6 hover:bg-gray-50 transition cursor-pointer">
                    <div className="flex gap-4">
                        <div className="w-10 h-10 rounded-full bg-gray-300 flex-shrink-0">
                            {getHostname(feed.feedUrl) && (
                                    <img
                                        src={`https://www.google.com/s2/favicons?domain=${getHostname(feed.feedUrl)}`}
                                        alt="Favicon"
                                        className="w-full h-full rounded-full object-cover"
                                    />
                                )}
                            </div>
                                     
                        <div className="flex-1">
                            <div className="flex justify-between items-center">
                                <h3 className="font-semibold text-gray-900">{feed.title.length > 50 ? feed.title.slice(0, 50) + "..." : feed.title}</h3>
                                <span className="text-sm text-gray-400">{new Date(feed.pubDate).toLocaleTimeString()}</span>
                            </div>
                            <p className="text-gray-600 text-sm mt-2">{feed.description}</p>
                            {feed.image && (
                                <div className="mt-4 rounded-2xl overflow-hidden">
                                    <img
                                        src={feed.image}
                                        alt="Post"
                                        className="w-full h-full object-cover"
                                    />
                                </div>
                            )}
                            <div className="flex gap-6 mt-4 text-gray-500 text-sm">
                                <button className="flex items-center gap-1 hover:text-black transition">
                                    <Bookmark size={16} /> Save
                                </button>

                                <a 
                                    href={feed.link}
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


export default function Dashboard() {
    return (
        <AppLayout>
            <div className="flex w-full max-w-[1200px] h-full">
                {/* Feed-Bereich */}
                <main className="flex-1 flex flex-col border-r border-gray-200 h-full">
                    {/* Fixierter Header */}
                    <div className="h-16 flex items-center justify-between px-6 border-b border-gray-200 sticky top-0 bg-white z-10">
                        <h2 className="font-semibold text-lg">Home</h2>
                        <Search size={20} className="text-gray-500 cursor-pointer" />
                    </div>
                    {/* Scrollbarer Feed-Inhalt */}
                    <div className="flex-1 overflow-y-auto">
                        <FeedCard />
                    </div>
                </main>
                <AppTags />
            </div>
          
        </AppLayout>
    );
}
