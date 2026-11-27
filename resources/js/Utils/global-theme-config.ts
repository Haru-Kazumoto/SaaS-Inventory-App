import { GlobalThemeOverrides } from "naive-ui";

// Warna brand utama, diselaraskan dengan halaman Login.
const BRAND = "#2A126C";
const BRAND_HOVER = "#3A1F8A";
const BRAND_PRESSED = "#1F0D52";
const FOCUS_RING = "0 0 0 3px rgba(42, 18, 108, 0.12)";
const BORDER = "1px solid #CBD5E1";

export const themeConfig: GlobalThemeOverrides = {
    common: {
        primaryColor: BRAND,
        primaryColorHover: BRAND_HOVER,
        primaryColorPressed: BRAND_PRESSED,
        primaryColorSuppl: BRAND_HOVER,
        borderRadius: "4px",
        borderRadiusSmall: "3px",
    },
    Button: {
        borderRadiusTiny: "3px",
        borderRadiusSmall: "4px",
        borderRadiusMedium: "4px",
        borderRadiusLarge: "4px",
    },
    Input: {
        borderRadius: "4px",
        caretColor: BRAND,
        borderHover: `1px solid ${BRAND}`,
        borderFocus: `1px solid ${BRAND}`,
        border: BORDER,
        boxShadowFocus: FOCUS_RING,
        loadingColor: BRAND,
        fontSizeLarge: "16px",
    },
    Menu: {
        borderRadius: "4px",
        itemColorHover: "#F1F5F9",
        itemTextColor: "#475569",
        itemTextColorHover: "#0F172A",
        itemIconColor: "#64748B",
        itemIconColorHover: "#0F172A",
        itemColorActive: BRAND,
        itemColorActiveHover: BRAND_HOVER,
        itemTextColorActive: "#FFFFFF",
        itemTextColorActiveHover: "#FFFFFF",
        itemIconColorActive: "#FFFFFF",
        itemIconColorActiveHover: "#FFFFFF",
        itemColorActiveCollapsed: BRAND,
    },
    Select: {
        peers: {
            InternalSelectMenu: {
                borderRadius: "4px",
                loadingColor: BRAND,
                optionCheckColor: BRAND,
            },
            InternalSelection: {
                borderRadius: "4px",
                caretColor: BRAND,
                borderHover: `1px solid ${BRAND}`,
                borderFocus: `1px solid ${BRAND}`,
                border: BORDER,
                boxShadowActive: FOCUS_RING,
                loadingColor: BRAND,
                fontSizeLarge: "16px",
                borderActive: `1px solid ${BRAND}`,
                boxShadowFocus: FOCUS_RING,
            },
        },
    },
    Pagination: {
        itemColorHover: "#FFFFFF",
        itemTextColorHover: BRAND,
        itemBorderHover: `1px solid ${BRAND}`,

        itemColorActive: BRAND,
        itemBorderActive: `1px solid ${BRAND}`,
        itemTextColorActive: "#FFFFFF",

        itemTextColor: "#475569",
        itemColor: "#FFFFFF",
        itemBorder: "1px solid #E2E8F0",

        itemColorActiveHover: BRAND_HOVER,
        itemBorderActiveHover: `1px solid ${BRAND_HOVER}`,
        itemTextColorActiveHover: "#FFFFFF",
    },
};
