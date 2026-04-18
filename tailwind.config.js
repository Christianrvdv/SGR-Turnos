/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./templates/**/*.html.twig",
        "./assets/**/*.js",
    ],
    darkMode: "class",
    theme: {
        extend: {
            colors: {
                "on-error": "#ffffff",
                "on-tertiary-fixed-variant": "#812800",
                "background": "#f9f9fb",
                "on-error-container": "#93000a",
                "primary": "#0040a1",
                "primary-fixed-dim": "#b2c5ff",
                "on-tertiary-fixed": "#380d00",
                "inverse-primary": "#b2c5ff",
                "on-tertiary-container": "#ffcebd",
                "surface-container-high": "#e8e8ea",
                "inverse-surface": "#2f3132",
                "surface-bright": "#f9f9fb",
                "outline": "#737785",
                "tertiary-fixed-dim": "#ffb59b",
                "secondary-fixed": "#dae2ff",
                "secondary-fixed-dim": "#b3c5fd",
                "error": "#ba1a1a",
                "surface-container-highest": "#e2e2e4",
                "inverse-on-surface": "#f0f0f2",
                "secondary-container": "#b3c5fd",
                "error-container": "#ffdad6",
                "tertiary": "#822800",
                "on-primary": "#ffffff",
                "on-secondary-container": "#3e5181",
                "on-primary-container": "#ccd8ff",
                "surface-variant": "#e2e2e4",
                "surface-tint": "#0056d2",
                "outline-variant": "#c3c6d6",
                "surface-container-low": "#f3f3f5",
                "surface-container": "#edeef0",
                "on-secondary": "#ffffff",
                "surface-container-lowest": "#ffffff",
                "primary-fixed": "#dae2ff",
                "primary-container": "#0056d2",
                "on-primary-fixed": "#001847",
                "on-surface-variant": "#424654",
                "tertiary-fixed": "#ffdbcf",
                "surface": "#f9f9fb",
                "on-secondary-fixed-variant": "#324575",
                "surface-dim": "#d9dadc",
                "on-surface": "#1a1c1d",
                "on-background": "#1a1c1d",
                "on-secondary-fixed": "#001847",
                "secondary": "#4a5d8e",
                "on-tertiary": "#ffffff",
                "on-primary-fixed-variant": "#0040a1",
                "tertiary-container": "#a93802"
            },
            borderRadius: {
                DEFAULT: "0.25rem",
                lg: "0.5rem",
                xl: "0.75rem",
                full: "9999px"
            },
            fontFamily: {
                headline: ["Inter", "sans-serif"],
                body: ["Inter", "sans-serif"],
                label: ["Inter", "sans-serif"]
            }
        }
    },
    plugins: [],
}
