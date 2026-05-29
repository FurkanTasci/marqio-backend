import { MoreHorizontal } from "lucide-react";

const AppTags = () => {
    const trends = [
        { title: "#React", posts: "100" },
        { title: "#AI", posts: "20" },
        { title: "#OpenAI", posts: "02" },
        { title: "#TailwindCSS", posts: "12" },
    ];

    return (
        <aside className="w-80 p-6 hidden lg:block">
            <div className="bg-gray-100 rounded-2xl overflow-hidden">
                <h3 className="font-bold text-lg px-4 py-4">Deine Tags</h3>
                {trends.map((trend, i) => (
                    <div key={i} className="px-4 py-3 hover:bg-gray-200 cursor-pointer transition flex justify-between">
                        <div>
                            <p className="font-semibold text-sm">{trend.title}</p>
                            <p className="text-xs text-gray-500 mt-1">{trend.posts} Bookmarks</p>
                        </div>
                        <MoreHorizontal size={18} className="text-gray-400 mt-1" />
                    </div>
                ))}
                <div className="px-4 py-4 hover:bg-gray-200 cursor-pointer text-sm text-blue-500">Show more</div>
            </div>
        </aside>
    );
};

export default AppTags;