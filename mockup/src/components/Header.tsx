import { useState, useEffect } from "react";

interface HeaderProps {
  onNavigate: (page: "home" | "detail") => void;
}

export default function Header({ onNavigate }: HeaderProps) {
  const [scrolled, setScrolled] = useState(false);
  const [menuOpen, setMenuOpen] = useState(false);

  useEffect(() => {
    const handler = () => setScrolled(window.scrollY > 40);
    window.addEventListener("scroll", handler, { passive: true });
    return () => window.removeEventListener("scroll", handler);
  }, []);

  return (
    <header
      className="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
      style={{
        backgroundColor: scrolled ? "rgba(249,248,245,0.97)" : "transparent",
        backdropFilter: scrolled ? "blur(12px)" : "none",
        borderBottom: scrolled ? "1px solid #E8E4DC" : "none",
        boxShadow: scrolled ? "0 1px 12px rgba(0,0,0,0.06)" : "none",
      }}
    >
      <div className="max-w-[1440px] mx-auto px-6 lg:px-12 flex items-center justify-between h-16 lg:h-20">
        {/* Logo */}
        <button
          onClick={() => onNavigate("home")}
          className="flex items-center gap-2 group"
        >
          <div
            className="w-8 h-8 rounded-lg flex items-center justify-center"
            style={{ backgroundColor: "#1B5E3B" }}
          >
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
              <path d="M3 9C3 9 6 5 9 5C12 5 15 9 15 9C15 9 12 13 9 13C6 13 3 9 3 9Z" fill="white" fillOpacity="0.9"/>
              <circle cx="9" cy="9" r="2.5" fill="white"/>
            </svg>
          </div>
          <span
            className="text-xl font-bold tracking-tight"
            style={{
              fontFamily: "'Plus Jakarta Sans', sans-serif",
              color: scrolled ? "#141414" : "#FFFFFF",
            }}
          >
            Rentiva
          </span>
        </button>

        {/* Desktop nav */}
        <nav className="hidden md:flex items-center gap-8">
          {["Home", "Explore Rentals", "How It Works", "About"].map((item) => (
            <a
              key={item}
              href="#"
              className="text-sm font-medium transition-colors"
              style={{
                color: scrolled ? "#141414" : "rgba(255,255,255,0.9)",
                fontFamily: "'Inter', sans-serif",
              }}
              onMouseEnter={(e) =>
                (e.currentTarget.style.color = scrolled ? "#1B5E3B" : "#FFFFFF")
              }
              onMouseLeave={(e) =>
                (e.currentTarget.style.color = scrolled
                  ? "#141414"
                  : "rgba(255,255,255,0.9)")
              }
            >
              {item}
            </a>
          ))}
        </nav>

        {/* Actions */}
        <div className="hidden md:flex items-center gap-3">
          <button
            className="text-sm font-medium px-4 py-2 rounded-full transition-all"
            style={{
              color: scrolled ? "#141414" : "rgba(255,255,255,0.9)",
              fontFamily: "'Inter', sans-serif",
            }}
          >
            Sign In
          </button>
          <button
            className="text-sm font-semibold px-5 py-2.5 rounded-full transition-all"
            style={{
              backgroundColor: "#1B5E3B",
              color: "#FFFFFF",
              fontFamily: "'Inter', sans-serif",
            }}
            onMouseEnter={(e) =>
              (e.currentTarget.style.backgroundColor = "#154d2e")
            }
            onMouseLeave={(e) =>
              (e.currentTarget.style.backgroundColor = "#1B5E3B")
            }
          >
            List Your Item
          </button>
        </div>

        {/* Mobile hamburger */}
        <button
          className="md:hidden p-2"
          onClick={() => setMenuOpen(!menuOpen)}
        >
          <div className="flex flex-col gap-1.5">
            {[0, 1, 2].map((i) => (
              <span
                key={i}
                className="block w-5 h-0.5 rounded transition-all"
                style={{ backgroundColor: scrolled ? "#141414" : "#FFFFFF" }}
              />
            ))}
          </div>
        </button>
      </div>

      {/* Mobile menu */}
      {menuOpen && (
        <div
          className="md:hidden px-6 py-4 flex flex-col gap-4"
          style={{ backgroundColor: "rgba(249,248,245,0.98)" }}
        >
          {["Home", "Explore Rentals", "How It Works", "About"].map((item) => (
            <a
              key={item}
              href="#"
              className="text-sm font-medium"
              style={{ color: "#141414", fontFamily: "'Inter', sans-serif" }}
            >
              {item}
            </a>
          ))}
          <div className="flex gap-3 pt-2 border-t" style={{ borderColor: "#E8E4DC" }}>
            <button
              className="text-sm font-medium px-4 py-2"
              style={{ color: "#141414" }}
            >
              Sign In
            </button>
            <button
              className="text-sm font-semibold px-5 py-2.5 rounded-full"
              style={{ backgroundColor: "#1B5E3B", color: "#FFFFFF" }}
            >
              List Your Item
            </button>
          </div>
        </div>
      )}
    </header>
  );
}
