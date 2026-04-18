/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./assets/**/*.js",
        "./templates/**/*.html.twig",
        "./src/**/*.php",
    ],
    theme: {
        extend: {
            colors: {
                primary: "#135bec",
                "primary-dark": "#0e4ac8",
                secondary: "#10b981",
                accent: "#8b5cf6",
                "background-light": "#f6f6f8",
                "background-dark": "#101622",
                success: "#10b981",
                warning: "#f59e0b",
                danger: "#ef4444",
                info: "#3b82f6",
                gray: {
                    50: '#f9fafb',
                    100: '#f3f4f6',
                    200: '#e5e7eb',
                    300: '#d1d5db',
                    400: '#9ca3af',
                    500: '#6b7280',
                    600: '#4b5563',
                    700: '#374151',
                    800: '#1f2937',
                    900: '#111827',
                }
            },
            fontFamily: {
                sans: ["Inter", "system-ui", "sans-serif"],
                display: ["Inter", "sans-serif"]
            },
            borderRadius: {
                DEFAULT: "0.5rem",
                lg: "1rem",
                xl: "1.5rem",
                full: "9999px"
            },
            animation: {
                'spin': 'spin 1s linear infinite',
                'fade-in': 'fadeIn 0.3s ease-in-out',
                'slide-in': 'slideIn 0.3s ease-out',
                // Agregamos la animación personalizada que sí usas
                'slide-in-from-top-2': 'slideInFromTop2 0.3s ease-out',
            },
            keyframes: {
                spin: {
                    '0%': { transform: 'rotate(0deg)' },
                    '100%': { transform: 'rotate(360deg)' }
                },
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' }
                },
                slideIn: {
                    '0%': { transform: 'translateX(-100%)' },
                    '100%': { transform: 'translateX(0)' }
                },
                slideInFromTop2: {
                    '0%': { transform: 'translateY(-20px)', opacity: '0' },
                    '100%': { transform: 'translateY(0)', opacity: '1' }
                }
            }
        },
    },
    plugins: [],
}
