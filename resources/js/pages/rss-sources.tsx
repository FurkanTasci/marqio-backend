import { Rss, Search } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { usePage } from '@inertiajs/react';
import AppTags from '@/components/app-tags';

const RssSourcesCard = () => {
    const { rss_sources } = usePage().props;

    console.log(rss_sources);

    return (
        <div>
            {rss_sources.map((rss_source) => (
                <div
                    key={rss_source.id}
                    className="flex items-center justify-between border border-gray-200 p-5 hover:bg-gray-50 transition cursor-pointer"
                >
                    {/* Linke Seite (Text) */}
                    <div className="flex flex-col gap-2">
                        <h3 className="font-semibold text-gray-900">
                            {rss_source.url.length > 50
                                ? rss_source.url.slice(0, 50) + "..."
                                : rss_source.url}
                        </h3>

                        <span className="text-sm text-gray-400">
                            {new Date(rss_source.created_at).toLocaleString()}
                        </span>
                    </div>

                    {/* Rechte Seite (Icon) */}
                    <div className="w-14 h-14 flex items-center justify-center bg-orange-400 rounded-2xl">
                        <Rss className="w-6 h-6 text-white" />
                    </div>
                </div>
            ))}
        </div>
    );
};

export default function RssSources() {
    return (
        <AppLayout>
            <div className="flex w-full max-w-[1200px] h-full">
                {/* Feed-Bereich */}
                <main className="flex-1 flex flex-col border-r border-gray-200 h-full">
                    {/* Fixierter Header */}
                    <div className="h-16 flex items-center justify-between px-6 border-b border-gray-200 sticky top-0 bg-white z-10">
                        <h2 className="font-semibold text-lg">RSS Sources</h2>
                        <Search size={20} className="text-gray-500 cursor-pointer" />
                    </div>
                    {/* Scrollbarer Feed-Inhalt */}
                    <div className="flex-1 overflow-y-auto">
                        {/* <RssSourcesCard /> */}
                        <RssSourcesCard />
                    </div>
                </main>
                <AppTags />
            </div>
        </AppLayout>
    );
}