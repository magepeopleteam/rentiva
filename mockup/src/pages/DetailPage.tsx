import { useState } from "react";

interface DetailPageProps {
  onNavigate: (page: "home" | "detail") => void;
}

const IMG = {
  main: "https://images.unsplash.com/photo-1571068316344-75bc76f77890?w=1200&h=800&fit=crop&auto=format&q=85",
  thumb1: "https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&h=300&fit=crop&auto=format&q=80",
  thumb2: "https://images.unsplash.com/photo-1541625602603-c7e42d049a29?w=400&h=300&fit=crop&auto=format&q=80",
  thumb3: "https://images.unsplash.com/photo-1532298229144-0ec0c57515c7?w=400&h=300&fit=crop&auto=format&q=80",
  thumb4: "https://images.unsplash.com/photo-1485965120184-e220f721d03e?w=400&h=300&fit=crop&auto=format&q=80",
  reviewer1: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&auto=format",
  reviewer2: "https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&h=100&fit=crop&auto=format",
  reviewer3: "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&h=100&fit=crop&auto=format",
  similar1: "https://images.unsplash.com/photo-1532298229144-0ec0c57515c7?w=600&h=500&fit=crop&auto=format&q=80",
  similar2: "https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&h=500&fit=crop&auto=format&q=80",
  similar3: "https://images.unsplash.com/photo-1485965120184-e220f721d03e?w=600&h=500&fit=crop&auto=format&q=80",
  similar4: "https://images.unsplash.com/photo-1541625602603-c7e42d049a29?w=600&h=500&fit=crop&auto=format&q=80",
};

function StarRating({ rating, size = 14 }: { rating: number; size?: number }) {
  return (
    <span className="flex items-center gap-0.5">
      {[1, 2, 3, 4, 5].map((s) => (
        <svg key={s} width={size} height={size} viewBox="0 0 14 14" fill={s <= Math.round(rating) ? "#F59E0B" : "#E8E4DC"}>
          <path d="M7 1l1.62 3.28 3.62.53-2.62 2.55.62 3.6L7 9.27l-3.24 1.69.62-3.6L1.76 4.81l3.62-.53L7 1z"/>
        </svg>
      ))}
    </span>
  );
}

const similarProducts = [
  { name: "Urban Cruiser", type: "City Bike", rating: 4.8, reviews: 64, location: "Dhaka", price: 14, img: IMG.similar1 },
  { name: "Trail Master", type: "Adventure Bike", rating: 5.0, reviews: 112, location: "Chittagong", price: 22, img: IMG.similar2 },
  { name: "Weekend Pro", type: "Hybrid Bike", rating: 4.9, reviews: 53, location: "Sylhet", price: 16, img: IMG.similar3 },
  { name: "Road Racer", type: "Road Bike", rating: 4.7, reviews: 38, location: "Dhaka", price: 20, img: IMG.similar4 },
];

const reviews = [
  { name: "Sarah Chen", rating: 5, date: "Aug 2026", text: "Absolutely loved this bike. Perfect condition, smooth gears. Will rent again!", img: IMG.reviewer2 },
  { name: "James Okoye", rating: 5, date: "Jul 2026", text: "Excellent quality and the pickup was super easy. Great experience overall.", img: IMG.reviewer1 },
  { name: "Mia Lopez", rating: 5, date: "Jun 2026", text: "Beautiful bike and the helmet was brand new. Highly recommend.", img: IMG.reviewer3 },
];

export default function DetailPage({ onNavigate }: DetailPageProps) {
  const [liked, setLiked] = useState(false);
  const [selectedThumb, setSelectedThumb] = useState(0);
  const [pickupDate, setPickupDate] = useState("12 Sep 2026");
  const [returnDate, setReturnDate] = useState("14 Sep 2026");
  const [qty, setQty] = useState(1);

  const thumbs = [IMG.main, IMG.thumb1, IMG.thumb2, IMG.thumb3, IMG.thumb4];
  const mainImg = thumbs[selectedThumb];

  const days = 2;
  const pricePerDay = 18;
  const serviceFee = 4;
  const total = days * pricePerDay * qty + serviceFee;

  return (
    <div style={{ backgroundColor: "#F9F8F5", paddingTop: 80 }}>

      {/* ── BREADCRUMB ── */}
      <div className="max-w-[1440px] mx-auto px-6 lg:px-12 py-4">
        <nav className="flex items-center gap-2 text-xs" style={{ color: "#7A7670", fontFamily: "'Inter', sans-serif" }}>
          <button onClick={() => onNavigate("home")} className="hover:text-green-800 transition-colors" style={{ color: "#7A7670" }}>Home</button>
          <span>/</span>
          <a href="#" style={{ color: "#7A7670" }} className="hover:text-green-800 transition-colors">Bicycles</a>
          <span>/</span>
          <a href="#" style={{ color: "#7A7670" }} className="hover:text-green-800 transition-colors">Mountain Bikes</a>
          <span>/</span>
          <span style={{ color: "#141414" }}>Explorer X1</span>
        </nav>
      </div>

      {/* ── GALLERY + INFO ── */}
      <div className="max-w-[1440px] mx-auto px-6 lg:px-12 py-8">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-12">

          {/* Gallery col (spans 2) */}
          <div className="lg:col-span-2">
            {/* Main image */}
            <div
              className="relative rounded-2xl overflow-hidden bg-gray-200 mb-4"
              style={{ aspectRatio: "16/10" }}
            >
              <img
                src={mainImg}
                alt="Explorer X1 Mountain Bike"
                className="w-full h-full object-cover"
              />
              <div className="absolute top-4 right-4 flex gap-2">
                <button
                  onClick={() => setLiked(!liked)}
                  className="w-9 h-9 rounded-full flex items-center justify-center transition-all"
                  style={{ backgroundColor: "rgba(255,255,255,0.92)", backdropFilter: "blur(8px)" }}
                >
                  <svg width="16" height="16" viewBox="0 0 16 16" fill={liked ? "#EF4444" : "none"} stroke={liked ? "#EF4444" : "#141414"} strokeWidth="1.5">
                    <path d="M8 13.5S2 10 2 5.5A3.5 3.5 0 018 3a3.5 3.5 0 016 2.5C14 10 8 13.5 8 13.5z"/>
                  </svg>
                </button>
                <button
                  className="w-9 h-9 rounded-full flex items-center justify-center transition-all"
                  style={{ backgroundColor: "rgba(255,255,255,0.92)", backdropFilter: "blur(8px)" }}
                >
                  <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="#141414" strokeWidth="1.5">
                    <rect x="1" y="1" width="5" height="5" rx="1"/>
                    <rect x="8" y="1" width="5" height="5" rx="1"/>
                    <rect x="1" y="8" width="5" height="5" rx="1"/>
                    <rect x="8" y="8" width="5" height="5" rx="1"/>
                  </svg>
                </button>
              </div>
              <div
                className="absolute bottom-4 left-4 px-3 py-1.5 rounded-full text-xs font-medium"
                style={{ backgroundColor: "rgba(0,0,0,0.5)", color: "#FFFFFF", backdropFilter: "blur(8px)" }}
              >
                12 Photos
              </div>
            </div>

            {/* Thumbnails */}
            <div className="grid grid-cols-5 gap-3">
              {thumbs.map((t, i) => (
                <button
                  key={i}
                  onClick={() => setSelectedThumb(i)}
                  className="rounded-xl overflow-hidden bg-gray-200 transition-all"
                  style={{
                    aspectRatio: "1",
                    border: selectedThumb === i ? "2.5px solid #1B5E3B" : "2.5px solid transparent",
                    opacity: selectedThumb === i ? 1 : 0.7,
                  }}
                >
                  <img src={t} alt="" className="w-full h-full object-cover" />
                </button>
              ))}
            </div>

            {/* Product info below gallery on desktop */}
            <div className="mt-10">
              <span
                className="inline-block text-xs font-bold tracking-widest px-3 py-1.5 rounded-full mb-4"
                style={{ backgroundColor: "#E8F5EE", color: "#1B5E3B" }}
              >
                MOUNTAIN BIKE
              </span>
              <h1
                className="font-extrabold mb-4 leading-tight"
                style={{
                  fontFamily: "'Plus Jakarta Sans', sans-serif",
                  color: "#141414",
                  fontSize: "clamp(1.8rem, 3vw, 2.5rem)",
                  letterSpacing: "-0.02em",
                }}
              >
                Explorer X1 Mountain Bike
              </h1>
              <div className="flex flex-wrap items-center gap-4 mb-4">
                <div className="flex items-center gap-2">
                  <StarRating rating={4.9} />
                  <span className="text-sm font-semibold" style={{ color: "#141414" }}>4.9</span>
                  <span className="text-sm" style={{ color: "#7A7670" }}>· 124 reviews</span>
                </div>
                <div className="flex items-center gap-1.5">
                  <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="#7A7670" strokeWidth="1.5">
                    <path d="M7 1C5.07 1 3.5 2.57 3.5 4.5c0 2.77 3.5 6.5 3.5 6.5s3.5-3.73 3.5-6.5C10.5 2.57 8.93 1 7 1z"/>
                    <circle cx="7" cy="4.5" r="1.2"/>
                  </svg>
                  <span className="text-sm" style={{ color: "#7A7670" }}>Dhaka, Bangladesh</span>
                </div>
              </div>

              <p className="text-base leading-relaxed mb-6" style={{ color: "#7A7670", fontFamily: "'Inter', sans-serif" }}>
                Premium mountain bike designed for city rides, trails and weekend adventures. Built with a lightweight aluminum frame and 21-speed gearing for effortless climbing and confident descents.
              </p>

              {/* Feature tags */}
              <div className="flex flex-wrap gap-2 mb-6">
                {["21 Speed", "Aluminum Frame", "Lightweight", "All Terrain"].map((tag) => (
                  <span
                    key={tag}
                    className="text-xs font-medium px-3 py-1.5 rounded-full"
                    style={{ backgroundColor: "#F3F0EB", color: "#141414", fontFamily: "'Inter', sans-serif" }}
                  >
                    {tag}
                  </span>
                ))}
              </div>

              <div className="flex items-baseline gap-3">
                <span
                  className="text-4xl font-extrabold"
                  style={{ fontFamily: "'Plus Jakarta Sans', sans-serif", color: "#141414" }}
                >
                  $18
                </span>
                <span className="text-base" style={{ color: "#7A7670" }}>/ day</span>
                <span
                  className="text-sm font-medium px-3 py-1 rounded-full"
                  style={{ backgroundColor: "#F3F0EB", color: "#7A7670" }}
                >
                  $85 / week
                </span>
              </div>
            </div>

            {/* Specifications */}
            <div className="mt-12">
              <h2
                className="font-bold text-xl mb-6"
                style={{ fontFamily: "'Plus Jakarta Sans', sans-serif", color: "#141414" }}
              >
                Key Specifications
              </h2>
              <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                {[
                  { label: "Type", value: "Mountain Bike" },
                  { label: "Gears", value: "21 Speed" },
                  { label: "Frame", value: "Aluminum" },
                  { label: "Weight", value: "12.5 kg" },
                  { label: "Suitable For", value: "Adults" },
                  { label: "Included", value: "Helmet + Lock" },
                ].map((spec) => (
                  <div
                    key={spec.label}
                    className="p-4 rounded-2xl"
                    style={{ backgroundColor: "#FFFFFF", border: "1px solid #E8E4DC" }}
                  >
                    <p className="text-xs mb-1" style={{ color: "#7A7670", fontFamily: "'Inter', sans-serif" }}>{spec.label}</p>
                    <p className="text-sm font-semibold" style={{ color: "#141414", fontFamily: "'Plus Jakarta Sans', sans-serif" }}>{spec.value}</p>
                  </div>
                ))}
              </div>
            </div>

            {/* About */}
            <div className="mt-12">
              <h2
                className="font-bold text-xl mb-4"
                style={{ fontFamily: "'Plus Jakarta Sans', sans-serif", color: "#141414" }}
              >
                About this rental
              </h2>
              <p className="text-base leading-relaxed mb-8" style={{ color: "#7A7670", fontFamily: "'Inter', sans-serif" }}>
                The Explorer X1 is our most popular rental. Suited for riders of all experience levels, this bike handles both urban streets and light trails with ease. Maintained weekly and cleaned before every rental so it always arrives in pristine condition.
              </p>

              <h3
                className="font-semibold text-base mb-4"
                style={{ fontFamily: "'Plus Jakarta Sans', sans-serif", color: "#141414" }}
              >
                What's Included
              </h3>
              <div className="grid grid-cols-2 gap-3">
                {["Helmet", "Bike Lock", "Repair Kit", "Front & Rear Lights"].map((item) => (
                  <div key={item} className="flex items-center gap-3">
                    <div
                      className="w-6 h-6 rounded-full flex items-center justify-center shrink-0"
                      style={{ backgroundColor: "#E8F5EE" }}
                    >
                      <svg width="10" height="10" viewBox="0 0 10 10" fill="none" stroke="#1B5E3B" strokeWidth="2">
                        <path d="M2 5l2 2 4-4"/>
                      </svg>
                    </div>
                    <span className="text-sm" style={{ color: "#141414", fontFamily: "'Inter', sans-serif" }}>{item}</span>
                  </div>
                ))}
              </div>
            </div>

            {/* Pickup location */}
            <div className="mt-12">
              <h2
                className="font-bold text-xl mb-4"
                style={{ fontFamily: "'Plus Jakarta Sans', sans-serif", color: "#141414" }}
              >
                Pickup Location
              </h2>
              <div
                className="rounded-2xl overflow-hidden p-6"
                style={{ backgroundColor: "#FFFFFF", border: "1px solid #E8E4DC" }}
              >
                <div
                  className="rounded-xl mb-4 flex items-center justify-center"
                  style={{ backgroundColor: "#F3F0EB", height: 160 }}
                >
                  {/* Map placeholder */}
                  <svg width="40" height="40" viewBox="0 0 40 40" fill="none" opacity="0.3">
                    <circle cx="20" cy="18" r="10" stroke="#141414" strokeWidth="2"/>
                    <path d="M20 8C16.13 8 13 11.13 13 15c0 5.25 7 17 7 17s7-11.75 7-17c0-3.87-3.13-7-7-7z" fill="#141414"/>
                    <circle cx="20" cy="15" r="3" fill="white"/>
                  </svg>
                </div>
                <div className="flex items-center justify-between">
                  <div>
                    <p className="font-semibold text-sm" style={{ fontFamily: "'Plus Jakarta Sans', sans-serif", color: "#141414" }}>Dhanmondi, Dhaka</p>
                    <p className="text-xs mt-1" style={{ color: "#7A7670" }}>Available daily 8:00 AM – 8:00 PM</p>
                  </div>
                  <button
                    className="text-sm font-semibold px-4 py-2 rounded-xl transition-all"
                    style={{ backgroundColor: "#1B5E3B", color: "#FFFFFF", fontFamily: "'Inter', sans-serif" }}
                  >
                    Get Directions
                  </button>
                </div>
              </div>
            </div>

            {/* Reviews */}
            <div className="mt-12">
              <h2
                className="font-bold text-xl mb-6"
                style={{ fontFamily: "'Plus Jakarta Sans', sans-serif", color: "#141414" }}
              >
                What renters say
              </h2>
              <div
                className="flex items-center gap-4 p-6 rounded-2xl mb-6"
                style={{ backgroundColor: "#FFFFFF", border: "1px solid #E8E4DC" }}
              >
                <div className="text-center">
                  <p
                    className="font-extrabold"
                    style={{ fontSize: "3rem", fontFamily: "'Plus Jakarta Sans', sans-serif", color: "#141414", lineHeight: 1 }}
                  >
                    4.9
                  </p>
                  <p className="text-xs mt-1" style={{ color: "#7A7670" }}>out of 5</p>
                </div>
                <div>
                  <StarRating rating={5} size={18} />
                  <p className="text-sm mt-1" style={{ color: "#7A7670" }}>124 reviews</p>
                </div>
              </div>

              <div className="flex flex-col gap-4">
                {reviews.map((r) => (
                  <div
                    key={r.name}
                    className="p-5 rounded-2xl"
                    style={{ backgroundColor: "#FFFFFF", border: "1px solid #E8E4DC" }}
                  >
                    <div className="flex items-start gap-3 mb-3">
                      <div className="w-10 h-10 rounded-full overflow-hidden bg-gray-200 shrink-0">
                        <img src={r.img} alt={r.name} className="w-full h-full object-cover" />
                      </div>
                      <div className="flex-1">
                        <div className="flex items-center justify-between flex-wrap gap-2">
                          <p className="font-semibold text-sm" style={{ fontFamily: "'Plus Jakarta Sans', sans-serif", color: "#141414" }}>{r.name}</p>
                          <p className="text-xs" style={{ color: "#7A7670" }}>{r.date}</p>
                        </div>
                        <StarRating rating={r.rating} />
                      </div>
                    </div>
                    <p className="text-sm leading-relaxed" style={{ color: "#7A7670", fontFamily: "'Inter', sans-serif" }}>{r.text}</p>
                  </div>
                ))}
              </div>
            </div>
          </div>

          {/* Sticky booking card */}
          <div className="lg:col-span-1">
            <div className="sticky top-24">
              <div
                className="rounded-2xl p-6"
                style={{
                  backgroundColor: "#FFFFFF",
                  border: "1px solid #E8E4DC",
                  boxShadow: "0 8px 40px rgba(0,0,0,0.1)",
                }}
              >
                <h3
                  className="font-bold text-lg mb-5"
                  style={{ fontFamily: "'Plus Jakarta Sans', sans-serif", color: "#141414" }}
                >
                  Rent this item
                </h3>

                <div className="flex flex-col gap-3 mb-4">
                  <div>
                    <label className="block text-xs font-semibold tracking-widest mb-1.5" style={{ color: "#7A7670" }}>PICKUP DATE</label>
                    <input
                      type="text"
                      value={pickupDate}
                      onChange={(e) => setPickupDate(e.target.value)}
                      className="w-full text-sm font-medium rounded-xl px-3 py-3 outline-none transition-all"
                      style={{ border: "1.5px solid #E8E4DC", color: "#141414" }}
                      onFocus={(e) => (e.currentTarget.style.borderColor = "#1B5E3B")}
                      onBlur={(e) => (e.currentTarget.style.borderColor = "#E8E4DC")}
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-semibold tracking-widest mb-1.5" style={{ color: "#7A7670" }}>RETURN DATE</label>
                    <input
                      type="text"
                      value={returnDate}
                      onChange={(e) => setReturnDate(e.target.value)}
                      className="w-full text-sm font-medium rounded-xl px-3 py-3 outline-none transition-all"
                      style={{ border: "1.5px solid #E8E4DC", color: "#141414" }}
                      onFocus={(e) => (e.currentTarget.style.borderColor = "#1B5E3B")}
                      onBlur={(e) => (e.currentTarget.style.borderColor = "#E8E4DC")}
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-semibold tracking-widest mb-1.5" style={{ color: "#7A7670" }}>QUANTITY</label>
                    <div className="flex items-center gap-3">
                      <button
                        onClick={() => setQty(Math.max(1, qty - 1))}
                        className="w-8 h-8 rounded-lg flex items-center justify-center transition-all font-bold"
                        style={{ border: "1.5px solid #E8E4DC", color: "#141414", backgroundColor: "#F9F8F5" }}
                      >
                        −
                      </button>
                      <span
                        className="text-base font-semibold w-6 text-center"
                        style={{ color: "#141414", fontFamily: "'Plus Jakarta Sans', sans-serif" }}
                      >
                        {qty}
                      </span>
                      <button
                        onClick={() => setQty(qty + 1)}
                        className="w-8 h-8 rounded-lg flex items-center justify-center transition-all font-bold"
                        style={{ border: "1.5px solid #E8E4DC", color: "#141414", backgroundColor: "#F9F8F5" }}
                      >
                        +
                      </button>
                    </div>
                  </div>
                </div>

                {/* Price breakdown */}
                <div
                  className="py-4 mb-4 flex flex-col gap-2"
                  style={{ borderTop: "1px solid #E8E4DC", borderBottom: "1px solid #E8E4DC" }}
                >
                  <div className="flex justify-between text-sm">
                    <span style={{ color: "#7A7670" }}>{days} days × ${pricePerDay}</span>
                    <span style={{ color: "#141414", fontWeight: 500 }}>${days * pricePerDay * qty}</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span style={{ color: "#7A7670" }}>Service fee</span>
                    <span style={{ color: "#141414", fontWeight: 500 }}>${serviceFee}</span>
                  </div>
                  <div className="flex justify-between text-base font-bold mt-1">
                    <span style={{ color: "#141414" }}>Total</span>
                    <span style={{ color: "#141414" }}>${total}</span>
                  </div>
                </div>

                <button
                  className="w-full font-bold text-base py-4 rounded-xl mb-4 transition-all"
                  style={{ backgroundColor: "#1B5E3B", color: "#FFFFFF", fontFamily: "'Plus Jakarta Sans', sans-serif" }}
                  onMouseEnter={(e) => (e.currentTarget.style.backgroundColor = "#154d2e")}
                  onMouseLeave={(e) => (e.currentTarget.style.backgroundColor = "#1B5E3B")}
                >
                  Reserve Now
                </button>

                <div className="flex flex-col gap-2">
                  {["Secure booking", "Free cancellation"].map((item) => (
                    <div key={item} className="flex items-center gap-2">
                      <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="#1B5E3B" strokeWidth="2">
                        <path d="M2 7l3 3 7-7"/>
                      </svg>
                      <span className="text-xs" style={{ color: "#7A7670", fontFamily: "'Inter', sans-serif" }}>{item}</span>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Mobile sticky booking bar */}
      <div
        className="lg:hidden fixed bottom-0 left-0 right-0 z-40 p-4 flex items-center justify-between gap-4"
        style={{ backgroundColor: "#FFFFFF", borderTop: "1px solid #E8E4DC", boxShadow: "0 -4px 20px rgba(0,0,0,0.1)" }}
      >
        <div>
          <span className="text-xl font-bold" style={{ fontFamily: "'Plus Jakarta Sans', sans-serif", color: "#141414" }}>$18</span>
          <span className="text-sm" style={{ color: "#7A7670" }}> / day</span>
        </div>
        <button
          className="font-bold text-base px-8 py-3 rounded-xl flex-1 max-w-xs transition-all"
          style={{ backgroundColor: "#1B5E3B", color: "#FFFFFF", fontFamily: "'Plus Jakarta Sans', sans-serif" }}
        >
          Reserve Now
        </button>
      </div>

      {/* ── SIMILAR RENTALS ── */}
      <section className="max-w-[1440px] mx-auto px-6 lg:px-12 py-16">
        <h2
          className="font-extrabold mb-8"
          style={{
            fontFamily: "'Plus Jakarta Sans', sans-serif",
            color: "#141414",
            fontSize: "clamp(1.5rem, 3vw, 2rem)",
            letterSpacing: "-0.02em",
          }}
        >
          You might also like
        </h2>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 pb-16 lg:pb-0">
          {similarProducts.map((p) => (
            <SimilarCard key={p.name} product={p} />
          ))}
        </div>
      </section>
    </div>
  );
}

function SimilarCard({ product }: { product: { name: string; type: string; rating: number; reviews: number; location: string; price: number; img: string } }) {
  const [hovered, setHovered] = useState(false);
  const [liked, setLiked] = useState(false);

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
    >
      <div className="relative overflow-hidden" style={{ height: 200 }}>
        <img
          src={product.img}
          alt={product.name}
          className="w-full h-full object-cover transition-transform duration-500"
          style={{ transform: hovered ? "scale(1.06)" : "scale(1)" }}
        />
        <button
          onClick={(e) => { e.stopPropagation(); setLiked(!liked); }}
          className="absolute top-3 right-3 w-8 h-8 rounded-full flex items-center justify-center"
          style={{ backgroundColor: "rgba(255,255,255,0.92)", backdropFilter: "blur(8px)" }}
        >
          <svg width="14" height="14" viewBox="0 0 14 14" fill={liked ? "#EF4444" : "none"} stroke={liked ? "#EF4444" : "#141414"} strokeWidth="1.5">
            <path d="M7 12S2 9 2 5a3.5 3.5 0 017-1.5A3.5 3.5 0 0114 5c0 4-5 7-7 7z"/>
          </svg>
        </button>
        <span
          className="absolute top-3 left-3 text-xs font-semibold px-2.5 py-1 rounded-full"
          style={{ backgroundColor: "#1B5E3B", color: "#FFFFFF" }}
        >
          {product.type}
        </span>
      </div>
      <div className="p-4">
        <h3 className="font-semibold text-sm mb-1.5" style={{ fontFamily: "'Plus Jakarta Sans', sans-serif", color: "#141414" }}>
          {product.name}
        </h3>
        <div className="flex items-center gap-1 mb-3">
          <svg width="10" height="10" viewBox="0 0 10 10" fill="#F59E0B"><path d="M5 1l.93 2.83H9L6.56 5.62l.93 2.83L5 6.66 2.51 8.45l.93-2.83L1 3.83h3.07L5 1z"/></svg>
          <span className="text-xs font-medium" style={{ color: "#141414" }}>{product.rating}</span>
          <span className="text-xs" style={{ color: "#7A7670" }}>({product.reviews})</span>
          <span className="text-xs mx-1" style={{ color: "#E8E4DC" }}>·</span>
          <span className="text-xs" style={{ color: "#7A7670" }}>{product.location}</span>
        </div>
        <div className="flex items-center justify-between">
          <div>
            <span className="text-base font-bold" style={{ fontFamily: "'Plus Jakarta Sans', sans-serif", color: "#141414" }}>${product.price}</span>
            <span className="text-xs" style={{ color: "#7A7670" }}> / day</span>
          </div>
          <button
            className="text-xs font-semibold px-3 py-1.5 rounded-lg transition-all"
            style={{ backgroundColor: "#1B5E3B", color: "#FFFFFF" }}
          >
            View
          </button>
        </div>
      </div>
    </div>
  );
}
