import { useState, useEffect } from "react";
import Header from "./components/Header";
import HomePage from "./pages/HomePage";
import DetailPage from "./pages/DetailPage";

type Page = "home" | "detail";

export default function App() {
  const [page, setPage] = useState<Page>("home");

  const navigate = (target: Page) => {
    setPage(target);
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  useEffect(() => {
    window.scrollTo(0, 0);
  }, [page]);

  return (
    <div style={{ minHeight: "100%", backgroundColor: "#F9F8F5" }}>
      <Header onNavigate={navigate} />
      {page === "home" ? (
        <HomePage onNavigate={navigate} />
      ) : (
        <DetailPage onNavigate={navigate} />
      )}
    </div>
  );
}
