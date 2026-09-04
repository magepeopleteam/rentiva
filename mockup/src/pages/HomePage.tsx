import { useState } from "react";

interface HomePageProps {
  onNavigate: (page: "home" | "detail") => void;
}

const IMG = {
  hero: "https://images.unsplash.com/photo-1476041800959-2f6bb412c8ce?w=1920&h=1200&fit=crop&auto=format&q=90",
  heroOverlay: "https://images.unsplash.com/photo-1571333250630-f0230c373922?w=600&h=800&fit=crop&auto=format&q=85",
  promo: "https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=1920&h=900&fit=crop&auto=format&q=85",
  whyLeft: "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=960&fit=crop&auto=format&q=85",
  testimonial: "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400&h=400&fit=crop&auto=format&q=85",
  cats: [
    "https://images.unsplash.com/photo-1485965120184-e220f721d03e?w=600&h=700&fit=crop&auto=format&q=80",
    "https://images.unsplash.com/photo-1558981403-c5f9899a28bc?w=600&h=700&fit=crop&auto=format&q=80",
    "https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?w=600&h=700&fit=crop&auto=format&q=80",
    "https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=600&h=700&fit=crop&auto=format&q=80",
    "https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=600&h=700&fit=crop&auto=format&q=80",
    "https://images.unsplash.com/photo-1551632811-561732d1e306?w=600&h=700&fit=crop&auto=format&q=80",
  ],
  products: [
    "https://images.unsplash.com/photo-1571068316344-75bc76f77890?w=600&h=500&fit=crop&auto=format&q=80",
    "https://images.unsplash.com/photo-1532298229144-0ec0c57515c7?w=600&h=500&fit=crop&auto=format&q=80",
    "https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&h=500&fit=crop&auto=format&q=80",
    "https://images.unsplash.com/photo-1485965120184-e220f721d03e?w=600&h=500&fit=crop&auto=format&q=80",
  ],
};

const categories = [
  { name: "Bicycles", img: IMG.cats[0] },
  { name: "Scooters", img: IMG.cats[1] },
  { name: "Camping", img: IMG.cats[2] },
  { name: "Cameras", img: IMG.cats[3] },
  { name: "Water Sports", img: IMG.cats[4] },
  { name: "Outdoor Gear", img: IMG.cats[5] },
];

const products = [
  { name: "Explorer X1", type: "Mountain Bike", rating: 4.9, reviews: 87, location: "Dhaka", price: 18, img: IMG.products[0] },
  { name: "Urban Cruiser", type: "City Bike", rating: 4.8, reviews: 64, location: "Dhaka", price: 14, img: IMG.products[1] },
  { name: "Trail Master", type: "Adventure Bike", rating: 5.0, reviews: 112, location: "Chittagong", price: 22, img: IMG.products[2] },
  { name: "Weekend Pro", type: "Hybrid Bike", rating: 4.9, reviews: 53, location: "Sylhet", price: 16, img: IMG.products[3] },
];

const whyFeatures = [
  { icon: "✓", title: "Verified Equipment", desc: "Every item is reviewed and quality checked." },
  { icon: "✓", title: "Flexible Booking", desc: "Choose the dates and rental period that work for you." },
  { icon: "✓", title: "Transparent Pricing", desc: "No confusing fees or hidden surprises." },
  { icon: "✓", title: "Secure Payments", desc: "Simple and secure checkout." },
];

function StarRating({ rating }: { rating: number }) {
  return (
    <span className="flex items-center gap-0.5">
      {[1, 2, 3, 4, 5].map((s) => (
        <svg key={s} width="12" height="12" viewBox="0 0 12 12" fill={s <= Math.round(rating) ? "#F59E0B" : "#E8E4DC"}>
          <path d="M6 1l1.39 2.82L10.5 4.27l-2.25 2.19.53 3.09L6 8.02 3.22 9.55l.53-3.09L1.5 4.27l3.11-.45L6 1z"/>
        </svg>
      ))}
    </span>
  );
}

function RentalCard({ product, onClick }: { product: typeof products[0]; onClick: () => void }) {
  const [liked, setLiked] = useState(false);
  const [hovered, setHovered] = useState(false);

  return (
    <div
      className="rounded-2xl overflow-hidden cursor-pointer transition-all duration-300"
      style={{
        backgroundColor: "#FFFFFF",
        border: "1px solid #E8E4DC",
        boxShadow: hovered ? "0 12px 40px rgba(0,0,0,0.12)" : "0 2px 12px rgba(0,0,0,0.06)",
        transform: hovered ? "translateY(-4px)" : "none",
      }}
      onMouseEnter={() => setHovered(true)}
      onMouseLeave={() => setHovered(false)}
      onClick={onClick}
    >
      <div className="relative overflow-hidden" style={{ height: 220 }}>
        <img
          src={product.img}
          alt={product.name}
          className="w-full h-full object-cover transition-transform duration-500"
          style={{ transform: hovered ? "scale(1.06)" : "scale(1)" }}
        />
        <button
          onClick={(e) => { e.stopPropagation(); setLiked(!liked); }}
          className="absolute top-3 right-3 w-8 h-8 rounded-full flex items-center justify-center transition-all"
          style={{ backgroundColor: "rgba(255,255,255,0.92)", backdropFilter: "blur(8px)" }}
        >
          <svg width="16" height="16" viewBox="0 0 16 16" fill={liked ? "#EF4444" : "none"} stroke={liked ? "#EF4444" : "#141414"} strokeWidth="1.5">
            <path d="M8 13.5S2 10 2 5.5A3.5 3.5 0 018 3a3.5 3.5 0 016 2.5C14 10 8 13.5 8 13.5z"/>
          </svg>
        </button>
        <span
          className="absolute top-3 left-3 text-xs font-semibold px-2.5 py-1 rounded-full"
          style={{ backgroundColor: "#1B5E3B", color: "#FFFFFF", fontFamily: "'Inter', sans-serif", letterSpacing: "0.04em" }}
        >
          {product.type}
        </span>
      </div>
      <div className="p-4">
        <h3
          className="font-semibold text-base mb-1"
          style={{ fontFamily: "'Plus Jakarta Sans', sans-serif", color: "#141414" }}
        >
          {product.name}
        </h3>
        <div className="flex items-center gap-1.5 mb-2">
          <StarRating rating={product.rating} />
          <span className="text-xs font-medium" style={{ color: "#141414" }}>{product.rating}</span>
          <span className="text-xs" style={{ color: "#7A7670" }}>({product.reviews})</span>
        </div>
        <div className="flex items-center gap-1 mb-4">
          <svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="#7A7670" strokeWidth="1.5">
            <path d="M6 1C4.07 1 2.5 2.57 2.5 4.5c0 2.77 3.5 6.5 3.5 6.5s3.5-3.73 3.5-6.5C9.5 2.57 7.93 1 6 1z"/>
            <circle cx="6" cy="4.5" r="1.2"/>
          </svg>
          <span className="text-xs" style={{ color: "#7A7670" }}>{product.location}</span>
        </div>
        <div className="flex items-center justify-between">
          <div>
            <span className="text-xl font-bold" style={{ fontFamily: "'Plus Jakarta Sans', sans-serif", color: "#141414" }}>${product.price}</span>
            <span className="text-sm" style={{ color: "#7A7670" }}> / day</span>
          </div>
          <button
            className="text-sm font-semibold px-4 py-2 rounded-xl transition-all"
            style={{ backgroundColor: "#1B5E3B", color: "#FFFFFF", fontFamily: "'Inter', sans-serif" }}
            onMouseEnter={(e) => (e.currentTarget.style.backgroundColor = "#154d2e")}
            onMouseLeave={(e) => (e.currentTarget.style.backgroundColor = "#1B5E3B")}
          >
            View Details
          </button>
        </div>
      </div>
    </div>
  );
}

export default function HomePage({ onNavigate }: HomePageProps) {
  const [location, setLocation] = useState("");
  const [pickup, setPickup] = useState("Sep 12, 2026");
  const [returnDate, setReturnDate] = useState("Sep 14, 2026");
  const [category, setCategory] = useState("Bicycles");

  return (
    <div style={{ backgroundColor: "#F9F8F5" }}>

      {/* ── HERO ── */}
      <section className="relative w-full flex flex-col" style={{ minHeight: "100svh" }}>
        {/* Full-bleed background */}
        <div className="absolute inset-0 bg-slate-900">
          <img
            src={IMG.hero}
            alt="Cyclist on open road at golden hour"
            className="w-full h-full object-cover object-center"
            style={{ opacity: 0.68 }}
          />
          {/* Rich layered gradient: dark vignette top + strong bottom fade for search panel legibility */}
          <div
            className="absolute inset-0"
            style={{
              background:
                "linear-gradient(180deg, rgba(0,0,0,0.42) 0%, rgba(0,0,0,0.08) 38%, rgba(0,0,0,0.52) 72%, rgba(0,0,0,0.82) 100%)",
            }}
          />
        </div>

        {/* Centered hero text */}
        <div className="relative z-10 flex-1 flex flex-col items-center justify-center text-center px-6 pt-24 pb-16">
          <p
            className="text-xs font-bold tracking-[0.25em] mb-7"
            style={{ color: "rgba(255,255,255,0.65)", fontFamily: "'Inter', sans-serif" }}
          >
            RENT • RIDE • EXPLORE
          </p>
          <h1
            className="font-extrabold leading-[0.92] mb-7"
            style={{
              fontFamily: "'Plus Jakarta Sans', sans-serif",
              color: "#FFFFFF",
              fontSize: "clamp(3.5rem, 10vw, 7.5rem)",
              letterSpacing: "-0.03em",
              maxWidth: 900,
            }}
          >
            Rent. Ride.<br />Explore.
          </h1>
          <p
            className="text-lg lg:text-xl mb-10 max-w-lg mx-auto"
            style={{ color: "rgba(255,255,255,0.78)", fontFamily: "'Inter', sans-serif", lineHeight: 1.65 }}
          >
            Premium bikes, gear and equipment — ready whenever you are.
          </p>
          <div className="flex flex-wrap items-center justify-center gap-4">
            <button
              className="font-semibold px-8 py-4 rounded-full text-base transition-all"
              style={{ backgroundColor: "#1B5E3B", color: "#FFFFFF", fontFamily: "'Inter', sans-serif" }}
              onMouseEnter={(e) => (e.currentTarget.style.backgroundColor = "#154d2e")}
              onMouseLeave={(e) => (e.currentTarget.style.backgroundColor = "#1B5E3B")}
            >
              Explore Rentals
            </button>
            <a
              href="#"
              className="font-medium text-base flex items-center gap-2"
              style={{ color: "rgba(255,255,255,0.88)", fontFamily: "'Inter', sans-serif" }}
            >
              List Your Equipment
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" strokeWidth="1.5">
                <path d="M3 8h10M9 4l4 4-4 4"/>
              </svg>
            </a>
          </div>
        </div>

        {/* Search panel — centered, overlapping the bottom edge */}
        <div className="relative z-20 w-full flex justify-center px-4 sm:px-6 lg:px-12" style={{ marginBottom: "-72px" }}>
          <div
            className="w-full rounded-2xl p-6 lg:p-8"
            style={{
              maxWidth: 1100,
              backgroundColor: "#FFFFFF",
              boxShadow: "0 24px 80px rgba(0,0,0,0.22)",
              border: "1px solid #E8E4DC",
            }}
          >
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
              <div className="lg:col-span-1">
                <label className="block text-xs font-bold tracking-widest mb-2" style={{ color: "#7A7670", fontFamily: "'Inter', sans-serif" }}>WHERE</label>
                <input
                  type="text"
                  placeholder="Choose location"
                  value={location}
                  onChange={(e) => setLocation(e.target.value)}
                  className="w-full text-sm font-medium rounded-xl px-4 py-3.5 outline-none transition-all"
                  style={{ border: "1.5px solid #E8E4DC", backgroundColor: "#F9F8F5", color: "#141414", fontFamily: "'Inter', sans-serif" }}
                  onFocus={(e) => (e.currentTarget.style.borderColor = "#1B5E3B")}
                  onBlur={(e) => (e.currentTarget.style.borderColor = "#E8E4DC")}
                />
              </div>
              <div>
                <label className="block text-xs font-bold tracking-widest mb-2" style={{ color: "#7A7670", fontFamily: "'Inter', sans-serif" }}>PICKUP</label>
                <input
                  type="text"
                  value={pickup}
                  onChange={(e) => setPickup(e.target.value)}
                  className="w-full text-sm font-medium rounded-xl px-4 py-3.5 outline-none transition-all"
                  style={{ border: "1.5px solid #E8E4DC", backgroundColor: "#F9F8F5", color: "#141414", fontFamily: "'Inter', sans-serif" }}
                  onFocus={(e) => (e.currentTarget.style.borderColor = "#1B5E3B")}
                  onBlur={(e) => (e.currentTarget.style.borderColor = "#E8E4DC")}
                />
              </div>
              <div>
                <label className="block text-xs font-bold tracking-widest mb-2" style={{ color: "#7A7670", fontFamily: "'Inter', sans-serif" }}>RETURN</label>
                <input
                  type="text"
                  value={returnDate}
                  onChange={(e) => setReturnDate(e.target.value)}
                  className="w-full text-sm font-medium rounded-xl px-4 py-3.5 outline-none transition-all"
                  style={{ border: "1.5px solid #E8E4DC", backgroundColor: "#F9F8F5", color: "#141414", fontFamily: "'Inter', sans-serif" }}
                  onFocus={(e) => (e.currentTarget.style.borderColor = "#1B5E3B")}
                  onBlur={(e) => (e.currentTarget.style.borderColor = "#E8E4DC")}
                />
              </div>
              <div>
                <label className="block text-xs font-bold tracking-widest mb-2" style={{ color: "#7A7670", fontFamily: "'Inter', sans-serif" }}>CATEGORY</label>
                <select
                  value={category}
                  onChange={(e) => setCategory(e.target.value)}
                  className="w-full text-sm font-medium rounded-xl px-4 py-3.5 outline-none transition-all appearance-none"
                  style={{ border: "1.5px solid #E8E4DC", backgroundColor: "#F9F8F5", color: "#141414", fontFamily: "'Inter', sans-serif" }}
                  onFocus={(e) => (e.currentTarget.style.borderColor = "#1B5E3B")}
                  onBlur={(e) => (e.currentTarget.style.borderColor = "#E8E4DC")}
                >
                  {["Bicycles", "Scooters", "Camping", "Cameras", "Water Sports", "Outdoor Gear"].map((c) => (
                    <option key={c}>{c}</option>
                  ))}
                </select>
              </div>
              <button
                className="w-full font-semibold text-base py-3.5 rounded-xl transition-all"
                style={{ backgroundColor: "#1B5E3B", color: "#FFFFFF", fontFamily: "'Inter', sans-serif" }}
                onMouseEnter={(e) => (e.currentTarget.style.backgroundColor = "#154d2e")}
                onMouseLeave={(e) => (e.currentTarget.style.backgroundColor = "#1B5E3B")}
              >
                Find Rentals
              </button>
            </div>
          </div>
        </div>
      </section>

      {/* ── TRUST STRIP ── */}
      <section className="max-w-[1440px] mx-auto px-6 lg:px-12" style={{ paddingTop: 108 }}>
        <div
          className="grid grid-cols-2 lg:grid-cols-4 gap-6 py-10 border-y"
          style={{ borderColor: "#E8E4DC" }}
        >
          {[
            { value: "10,000+", label: "rentals completed" },
            { value: "4.9/5", label: "average rating" },
            { value: "100%", label: "verified equipment" },
            { value: "Secure", label: "booking guaranteed" },
          ].map((item) => (
            <div key={item.label} className="text-center">
              <p
                className="text-2xl lg:text-3xl font-bold mb-1"
                style={{ fontFamily: "'Plus Jakarta Sans', sans-serif", color: "#141414" }}
              >
                {item.value}
              </p>
              <p className="text-sm" style={{ color: "#7A7670", fontFamily: "'Inter', sans-serif" }}>{item.label}</p>
            </div>
          ))}
        </div>
      </section>

      {/* ── EXPLORE CATEGORIES ── */}
      <section className="max-w-[1440px] mx-auto px-6 lg:px-12 py-20 lg:py-28">
        <div className="flex flex-col lg:flex-row lg:items-end justify-between mb-12 gap-4">
          <div>
            <h2
              className="font-extrabold mb-3"
              style={{
                fontFamily: "'Plus Jakarta Sans', sans-serif",
                color: "#141414",
                fontSize: "clamp(2rem, 4vw, 3rem)",
                letterSpacing: "-0.02em",
              }}
            >
              Explore What You Need
            </h2>
            <p className="text-base" style={{ color: "#7A7670", fontFamily: "'Inter', sans-serif" }}>
              Find the right equipment for your next adventure.
            </p>
          </div>
          <a
            href="#"
            className="text-sm font-medium flex items-center gap-1.5 shrink-0 transition-opacity hover:opacity-70"
            style={{ color: "#1B5E3B", fontFamily: "'Inter', sans-serif" }}
          >
            View all categories
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" strokeWidth="1.5">
              <path d="M2.5 7h9M8 3.5l3.5 3.5-3.5 3.5"/>
            </svg>
          </a>
        </div>

        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
          {categories.map((cat) => (
            <CategoryCard key={cat.name} cat={cat} />
          ))}
        </div>
      </section>

      {/* ── FEATURED RENTALS ── */}
      <section className="max-w-[1440px] mx-auto px-6 lg:px-12 py-16 lg:py-24">
        <div className="flex flex-col lg:flex-row lg:items-end justify-between mb-12 gap-4">
          <div>
            <h2
              className="font-extrabold mb-3"
              style={{
                fontFamily: "'Plus Jakarta Sans', sans-serif",
                color: "#141414",
                fontSize: "clamp(2rem, 4vw, 3rem)",
                letterSpacing: "-0.02em",
              }}
            >
              Popular Rentals
            </h2>
            <p className="text-base" style={{ color: "#7A7670", fontFamily: "'Inter', sans-serif" }}>
              Highly rated equipment ready for your next adventure.
            </p>
          </div>
          <a
            href="#"
            className="text-sm font-medium flex items-center gap-1.5 shrink-0 hover:opacity-70 transition-opacity"
            style={{ color: "#1B5E3B", fontFamily: "'Inter', sans-serif" }}
          >
            View all rentals
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" strokeWidth="1.5">
              <path d="M2.5 7h9M8 3.5l3.5 3.5-3.5 3.5"/>
            </svg>
          </a>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {products.map((p) => (
            <RentalCard key={p.name} product={p} onClick={() => onNavigate("detail")} />
          ))}
        </div>
      </section>

      {/* ── LARGE PROMO ── */}
      <section className="relative w-full overflow-hidden" style={{ height: 560 }}>
        <div className="absolute inset-0 bg-gray-900">
          <img
            src={IMG.promo}
            alt="Adventure outdoor landscape"
            className="w-full h-full object-cover"
            style={{ opacity: 0.65 }}
          />
          <div className="absolute inset-0" style={{ background: "linear-gradient(135deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.2) 70%)" }} />
        </div>

        <div className="relative z-10 max-w-[1440px] mx-auto px-6 lg:px-12 h-full flex items-center">
          <div className="max-w-2xl">
            {/* Badge */}
            <div
              className="inline-flex items-center gap-2 px-4 py-2 rounded-full mb-8 text-xs font-bold tracking-widest"
              style={{ backgroundColor: "#1B5E3B", color: "#FFFFFF" }}
            >
              WEEKEND SPECIAL · UP TO 20% OFF
            </div>
            <h2
              className="font-extrabold leading-none mb-6 text-white"
              style={{
                fontFamily: "'Plus Jakarta Sans', sans-serif",
                fontSize: "clamp(2.5rem, 6vw, 5rem)",
                letterSpacing: "-0.02em",
              }}
            >
              Your next adventure<br />starts here.
            </h2>
            <p className="text-lg mb-8" style={{ color: "rgba(255,255,255,0.8)", fontFamily: "'Inter', sans-serif" }}>
              Discover premium equipment from trusted local owners.
            </p>
            <button
              className="font-semibold px-8 py-4 rounded-full text-base transition-all"
              style={{ backgroundColor: "#FFFFFF", color: "#141414", fontFamily: "'Inter', sans-serif" }}
              onMouseEnter={(e) => (e.currentTarget.style.backgroundColor = "#F3F0EB")}
              onMouseLeave={(e) => (e.currentTarget.style.backgroundColor = "#FFFFFF")}
            >
              Explore Rentals
            </button>
          </div>
        </div>
      </section>

      {/* ── HOW IT WORKS ── */}
      <section className="max-w-[1440px] mx-auto px-6 lg:px-12 py-20 lg:py-28">
        <div className="text-center mb-16">
          <h2
            className="font-extrabold mb-4"
            style={{
              fontFamily: "'Plus Jakarta Sans', sans-serif",
              color: "#141414",
              fontSize: "clamp(2rem, 4vw, 3rem)",
              letterSpacing: "-0.02em",
            }}
          >
            Rent in 3 Simple Steps
          </h2>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-12 lg:gap-16">
          {[
            { num: "01", title: "Find", desc: "Discover the perfect equipment near you." },
            { num: "02", title: "Book", desc: "Choose your dates and reserve in seconds." },
            { num: "03", title: "Enjoy", desc: "Pick it up and start your adventure." },
          ].map((step, i) => (
            <div key={step.num} className="relative">
              {i < 2 && (
                <div
                  className="hidden md:block absolute top-8 left-full w-full h-px -translate-x-1/2"
                  style={{ backgroundColor: "#E8E4DC", zIndex: 0 }}
                />
              )}
              <div className="relative z-10">
                <div
                  className="w-16 h-16 rounded-2xl flex items-center justify-center mb-6"
                  style={{ backgroundColor: "#1B5E3B" }}
                >
                  <span
                    className="text-xl font-bold text-white"
                    style={{ fontFamily: "'Plus Jakarta Sans', sans-serif" }}
                  >
                    {step.num}
                  </span>
                </div>
                <h3
                  className="text-xl font-bold mb-3"
                  style={{ fontFamily: "'Plus Jakarta Sans', sans-serif", color: "#141414" }}
                >
                  {step.title}
                </h3>
                <p className="text-base leading-relaxed" style={{ color: "#7A7670", fontFamily: "'Inter', sans-serif" }}>
                  {step.desc}
                </p>
              </div>
            </div>
          ))}
        </div>
      </section>

      {/* ── WHY RENTIVA ── */}
      <section className="max-w-[1440px] mx-auto px-6 lg:px-12 py-16 lg:py-24">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">
          <div className="rounded-2xl overflow-hidden bg-gray-200" style={{ aspectRatio: "4/5" }}>
            <img
              src={IMG.whyLeft}
              alt="Cyclist enjoying an outdoor adventure"
              className="w-full h-full object-cover"
            />
          </div>

          <div>
            <p
              className="text-xs font-bold tracking-[0.2em] mb-5"
              style={{ color: "#1B5E3B", fontFamily: "'Inter', sans-serif" }}
            >
              WHY RENTIVA
            </p>
            <h2
              className="font-extrabold mb-10 leading-tight"
              style={{
                fontFamily: "'Plus Jakarta Sans', sans-serif",
                color: "#141414",
                fontSize: "clamp(1.8rem, 3.5vw, 2.8rem)",
                letterSpacing: "-0.02em",
              }}
            >
              Everything you need<br />for a better rental.
            </h2>

            <div className="flex flex-col gap-8">
              {whyFeatures.map((f) => (
                <div key={f.title} className="flex items-start gap-4">
                  <div
                    className="w-8 h-8 rounded-full flex items-center justify-center shrink-0 mt-0.5"
                    style={{ backgroundColor: "#E8F5EE" }}
                  >
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="#1B5E3B" strokeWidth="2">
                      <path d="M2.5 7l3 3 6-6"/>
                    </svg>
                  </div>
                  <div>
                    <h4
                      className="font-semibold mb-1"
                      style={{ fontFamily: "'Plus Jakarta Sans', sans-serif", color: "#141414" }}
                    >
                      {f.title}
                    </h4>
                    <p className="text-sm leading-relaxed" style={{ color: "#7A7670", fontFamily: "'Inter', sans-serif" }}>
                      {f.desc}
                    </p>
                  </div>
                </div>
              ))}
            </div>

            <a
              href="#"
              className="inline-flex items-center gap-2 mt-10 text-sm font-semibold transition-opacity hover:opacity-70"
              style={{ color: "#1B5E3B", fontFamily: "'Inter', sans-serif" }}
            >
              Learn More
              <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" strokeWidth="1.5">
                <path d="M2.5 7h9M8 3.5l3.5 3.5-3.5 3.5"/>
              </svg>
            </a>
          </div>
        </div>
      </section>

      {/* ── TESTIMONIAL ── */}
      <section
        className="py-20 lg:py-28"
        style={{ backgroundColor: "#141414" }}
      >
        <div className="max-w-[1440px] mx-auto px-6 lg:px-12">
          <div className="max-w-4xl mx-auto text-center">
            <div className="flex justify-center mb-6">
              <StarRating rating={5} />
            </div>
            <blockquote
              className="font-bold leading-tight mb-10"
              style={{
                fontFamily: "'Plus Jakarta Sans', sans-serif",
                color: "#FFFFFF",
                fontSize: "clamp(1.5rem, 3vw, 2.5rem)",
                letterSpacing: "-0.01em",
              }}
            >
              "The easiest rental experience I've ever had. The bike was perfect and the entire process took less than two minutes."
            </blockquote>
            <div className="flex items-center justify-center gap-4">
              <div
                className="w-12 h-12 rounded-full overflow-hidden bg-gray-600"
              >
                <img src={IMG.testimonial} alt="Daniel Morgan" className="w-full h-full object-cover" />
              </div>
              <div className="text-left">
                <p className="font-semibold text-sm text-white" style={{ fontFamily: "'Plus Jakarta Sans', sans-serif" }}>
                  Daniel Morgan
                </p>
                <p className="text-xs" style={{ color: "rgba(255,255,255,0.5)", fontFamily: "'Inter', sans-serif" }}>
                  Weekend Traveler
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ── FINAL CTA ── */}
      <section
        className="relative py-28 overflow-hidden"
        style={{ backgroundColor: "#F3F0EB" }}
      >
        <div
          className="absolute inset-0 opacity-10"
          style={{
            backgroundImage: "radial-gradient(circle at 20% 50%, #1B5E3B 0%, transparent 60%), radial-gradient(circle at 80% 50%, #1B5E3B 0%, transparent 60%)",
          }}
        />
        <div className="relative z-10 max-w-2xl mx-auto px-6 text-center">
          <h2
            className="font-extrabold mb-4"
            style={{
              fontFamily: "'Plus Jakarta Sans', sans-serif",
              color: "#141414",
              fontSize: "clamp(2.5rem, 5vw, 4rem)",
              letterSpacing: "-0.02em",
            }}
          >
            Ready to explore?
          </h2>
          <p className="text-base mb-10" style={{ color: "#7A7670", fontFamily: "'Inter', sans-serif" }}>
            Find your perfect rental and start your next adventure.
          </p>
          <div className="flex flex-wrap items-center justify-center gap-4">
            <button
              className="font-semibold px-8 py-4 rounded-full text-base transition-all"
              style={{ backgroundColor: "#1B5E3B", color: "#FFFFFF", fontFamily: "'Inter', sans-serif" }}
              onMouseEnter={(e) => (e.currentTarget.style.backgroundColor = "#154d2e")}
              onMouseLeave={(e) => (e.currentTarget.style.backgroundColor = "#1B5E3B")}
            >
              Explore Rentals
            </button>
            <button
              className="font-semibold px-8 py-4 rounded-full text-base transition-all border"
              style={{ borderColor: "#141414", color: "#141414", backgroundColor: "transparent", fontFamily: "'Inter', sans-serif" }}
              onMouseEnter={(e) => { e.currentTarget.style.backgroundColor = "#141414"; e.currentTarget.style.color = "#FFFFFF"; }}
              onMouseLeave={(e) => { e.currentTarget.style.backgroundColor = "transparent"; e.currentTarget.style.color = "#141414"; }}
            >
              List Your Item
            </button>
          </div>
        </div>
      </section>

      {/* ── FOOTER ── */}
      <footer style={{ backgroundColor: "#141414" }} className="py-16">
        <div className="max-w-[1440px] mx-auto px-6 lg:px-12">
          <div className="grid grid-cols-1 lg:grid-cols-5 gap-12 mb-12">
            <div className="lg:col-span-2">
              <div className="flex items-center gap-2 mb-4">
                <div
                  className="w-8 h-8 rounded-lg flex items-center justify-center"
                  style={{ backgroundColor: "#1B5E3B" }}
                >
                  <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <path d="M3 9C3 9 6 5 9 5C12 5 15 9 15 9C15 9 12 13 9 13C6 13 3 9 3 9Z" fill="white" fillOpacity="0.9"/>
                    <circle cx="9" cy="9" r="2.5" fill="white"/>
                  </svg>
                </div>
                <span className="text-xl font-bold text-white" style={{ fontFamily: "'Plus Jakarta Sans', sans-serif" }}>Rentiva</span>
              </div>
              <p className="text-sm mb-6" style={{ color: "rgba(255,255,255,0.5)", fontFamily: "'Inter', sans-serif" }}>
                Rent smarter. Explore further.
              </p>
              <div className="flex gap-3">
                {["twitter", "instagram", "facebook"].map((social) => (
                  <a
                    key={social}
                    href="#"
                    className="w-9 h-9 rounded-full flex items-center justify-center transition-all"
                    style={{ backgroundColor: "rgba(255,255,255,0.08)", color: "rgba(255,255,255,0.6)" }}
                    onMouseEnter={(e) => (e.currentTarget.style.backgroundColor = "#1B5E3B")}
                    onMouseLeave={(e) => (e.currentTarget.style.backgroundColor = "rgba(255,255,255,0.08)")}
                  >
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="currentColor">
                      <circle cx="7" cy="7" r="5"/>
                    </svg>
                  </a>
                ))}
              </div>
            </div>

            {[
              { heading: "EXPLORE", links: ["Bikes", "Scooters", "Camping", "Sports Equipment"] },
              { heading: "COMPANY", links: ["About", "How It Works", "Contact"] },
              { heading: "SUPPORT", links: ["FAQ", "Terms", "Privacy"] },
            ].map((col) => (
              <div key={col.heading}>
                <h5
                  className="text-xs font-bold tracking-widest mb-5"
                  style={{ color: "rgba(255,255,255,0.4)", fontFamily: "'Inter', sans-serif" }}
                >
                  {col.heading}
                </h5>
                <ul className="flex flex-col gap-3">
                  {col.links.map((link) => (
                    <li key={link}>
                      <a
                        href="#"
                        className="text-sm transition-colors"
                        style={{ color: "rgba(255,255,255,0.6)", fontFamily: "'Inter', sans-serif" }}
                        onMouseEnter={(e) => (e.currentTarget.style.color = "#FFFFFF")}
                        onMouseLeave={(e) => (e.currentTarget.style.color = "rgba(255,255,255,0.6)")}
                      >
                        {link}
                      </a>
                    </li>
                  ))}
                </ul>
              </div>
            ))}
          </div>

          <div
            className="pt-8 border-t flex flex-col sm:flex-row justify-between items-center gap-4"
            style={{ borderColor: "rgba(255,255,255,0.1)" }}
          >
            <p className="text-xs" style={{ color: "rgba(255,255,255,0.35)", fontFamily: "'Inter', sans-serif" }}>
              © 2026 Rentiva. All rights reserved.
            </p>
          </div>
        </div>
      </footer>
    </div>
  );
}

function CategoryCard({ cat }: { cat: { name: string; img: string } }) {
  const [hovered, setHovered] = useState(false);

  return (
    <div
      className="relative rounded-2xl overflow-hidden cursor-pointer transition-all duration-300"
      style={{
        height: 240,
        backgroundColor: "#D0CCC4",
        boxShadow: hovered ? "0 16px 40px rgba(0,0,0,0.18)" : "0 2px 12px rgba(0,0,0,0.07)",
        transform: hovered ? "translateY(-4px)" : "none",
      }}
      onMouseEnter={() => setHovered(true)}
      onMouseLeave={() => setHovered(false)}
    >
      <img
        src={cat.img}
        alt={cat.name}
        className="w-full h-full object-cover transition-transform duration-500"
        style={{ transform: hovered ? "scale(1.08)" : "scale(1)" }}
      />
      <div
        className="absolute inset-0"
        style={{ background: "linear-gradient(to top, rgba(0,0,0,0.75) 0%, rgba(0,0,0,0.1) 60%)" }}
      />
      <div className="absolute bottom-0 left-0 right-0 p-4 flex items-end justify-between">
        <span
          className="font-semibold text-sm text-white"
          style={{ fontFamily: "'Plus Jakarta Sans', sans-serif" }}
        >
          {cat.name}
        </span>
        <div
          className="w-7 h-7 rounded-full flex items-center justify-center transition-all"
          style={{
            backgroundColor: hovered ? "#1B5E3B" : "rgba(255,255,255,0.2)",
            transform: hovered ? "translateX(3px)" : "none",
          }}
        >
          <svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="white" strokeWidth="1.5">
            <path d="M2 6h8M7 3l3 3-3 3"/>
          </svg>
        </div>
      </div>
    </div>
  );
}
