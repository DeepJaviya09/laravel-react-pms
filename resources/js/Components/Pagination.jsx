import { Link } from "@inertiajs/react";

export default function Pagination({ links, queryParams = {} }) {
  return (
    <nav className="text-center mt-4">
      {links.map(link => {
        if (!link.url) return null; // Skip if there's no URL
        
        // Convert the URL to https if it is currently http
        let url = new URL(link.url);
        if (url.protocol === "http:") {
          url.protocol = "https:";
        }

        // Update the query params
        const updatedQueryParams = {
          ...queryParams,
          ...Object.fromEntries(url.searchParams),
        };

        return (
          <Link
            preserveScroll
            preserveState
            href={`${url.pathname}?${new URLSearchParams(updatedQueryParams)}`}
            key={link.label}
            dangerouslySetInnerHTML={{ __html: link.label }}
            className={`inline-block py-2 px-3 rounded-lg text-gray-200 text-xs ${
              link.active ? "bg-gray-950" : ""
            } ${!link.url ? "text-gray-500 cursor-not-allowed" : "hover:bg-gray-950"}`}
          />
        );
      })}
    </nav>
  );
}
