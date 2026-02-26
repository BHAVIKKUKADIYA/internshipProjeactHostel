tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: {
                "primary": "#B76E79",
                "primary-hover": "#A05D67",
                "charcoal": "#1C1C1C",
                "soft-grey": "#5A5A5A",
                "background-ivory": "#FCF5F5",
                "background-white": "#FFFFFF",
                "border-neutral": "#EAE6DF",
                "footer-grey": "#F1F1F1"
            },
            fontFamily: {
                "display": ["Public Sans", "sans-serif"],
                "serif": ["Playfair Display", "serif"],
            },
            borderRadius: {
                "DEFAULT": "0.25rem",
                "lg": "0.5rem",
                "xl": "0.75rem",
                "full": "9999px"
            },
            boxShadow: {
                "luxury": "0 20px 50px rgba(183,110,121,0.05)",
            }
        },
    },
}
