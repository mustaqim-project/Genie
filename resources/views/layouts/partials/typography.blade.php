{{--
    GENIE ERP - Apple HIG Typographic Hierarchy System
    Diadopsi secara lengkap dari Cooca Core (references/design-system.md §8)
    Mendefinisikan gaya h1, h2, h3, h4, h5, h6, semantic heading classes,
    Apple typographic roles (headline, subheadline, footnote, caption, overline/kicker),
    serta perlakuan tabular-nums & artikel/prose.
--}}
<style id="genie-typography-system">
    /* ==========================================================================
       1. BASE HEADINGS (H1 - H6)
       ========================================================================== */
    h1,
    .h1,
    .heading-1,
    .heading-large-title {
        font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Inter", system-ui, sans-serif;
        font-size: clamp(1.25rem, 4vw, 2.125rem);
        line-height: 1.2;
        font-weight: 700;
        letter-spacing: -0.025em;
        color: var(--text-1, var(--apple-text, inherit));
        margin-top: 0;
        margin-bottom: 0.5rem;
        text-rendering: optimizeLegibility;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    h2,
    .h2,
    .heading-2,
    .heading-title-1 {
        font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Inter", system-ui, sans-serif;
        font-size: clamp(1.125rem, 3vw, 1.625rem);
        line-height: 1.25;
        font-weight: 600;
        letter-spacing: -0.02em;
        color: var(--text-1, var(--apple-text, inherit));
        margin-top: 0;
        margin-bottom: 0.5rem;
        text-rendering: optimizeLegibility;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    h3,
    .h3,
    .heading-3,
    .heading-title-2 {
        font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Inter", system-ui, sans-serif;
        font-size: 1.0625rem;
        /* 17px Mobile */
        line-height: 1.3;
        font-weight: 600;
        letter-spacing: -0.015em;
        color: var(--text-1, var(--apple-text, inherit));
        margin-top: 0;
        margin-bottom: 0.375rem;
        text-rendering: optimizeLegibility;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    @media (min-width: 640px) {
        h3,
        .h3,
        .heading-3,
        .heading-title-2 {
            font-size: 1.125rem;
            /* 18px Tablet */
            line-height: 1.3;
        }
    }

    @media (min-width: 1024px) {
        h3,
        .h3,
        .heading-3,
        .heading-title-2 {
            font-size: 1.25rem;
            /* 20px Desktop Title 2 / Card Header */
            line-height: 1.3;
        }
    }

    h4,
    .h4,
    .heading-4,
    .heading-headline {
        font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Inter", system-ui, sans-serif;
        font-size: 1rem;
        /* 16px Headline */
        line-height: 1.35;
        font-weight: 600;
        letter-spacing: -0.01em;
        color: var(--text-1, var(--apple-text, inherit));
        margin-top: 0;
        margin-bottom: 0.25rem;
        text-rendering: optimizeLegibility;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    h5,
    .h5,
    .heading-5,
    .heading-subheadline {
        font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Inter", system-ui, sans-serif;
        font-size: 0.875rem;
        /* 14px Subheadline */
        line-height: 1.4;
        font-weight: 600;
        letter-spacing: -0.005em;
        color: var(--text-1, var(--apple-text, inherit));
        margin-top: 0;
        margin-bottom: 0.25rem;
        text-rendering: optimizeLegibility;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    h6,
    .h6,
    .heading-6,
    .heading-caption {
        font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Inter", system-ui, sans-serif;
        font-size: 0.75rem;
        /* 12px Caption / Overline */
        line-height: 1.35;
        font-weight: 600;
        letter-spacing: 0.04em;
        color: var(--text-2, var(--apple-text-muted, rgba(60, 60, 67, 0.6)));
        margin-top: 0;
        margin-bottom: 0.25rem;
        text-rendering: optimizeLegibility;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    .dark h6,
    .dark .h6,
    .dark .heading-6,
    .dark .heading-caption {
        color: var(--text-2, var(--apple-text-muted, rgba(235, 235, 245, 0.6)));
    }

    /* ==========================================================================
       2. APPLE HIG SPECIALIZED TYPOGRAPHIC ROLES & MICROCOPY
       ========================================================================== */
    /* Large Title display helper */
    .title-large {
        font-size: clamp(1.375rem, 3.5vw, 2.125rem);
        line-height: 1.2;
        font-weight: 700;
        letter-spacing: -0.025em;
        color: var(--text-1, var(--apple-text, inherit));
    }

    /* Headline role (16px Bold/Semibold) */
    .headline {
        font-size: 1rem;
        line-height: 1.35;
        font-weight: 600;
        letter-spacing: -0.01em;
        color: var(--text-1, var(--apple-text, inherit));
    }

    /* Subheadline role (14px Medium) */
    .subheadline {
        font-size: 0.875rem;
        line-height: 1.4;
        font-weight: 500;
        letter-spacing: -0.005em;
        color: var(--text-2, var(--apple-text-muted, rgba(60, 60, 67, 0.7)));
    }

    .dark .subheadline {
        color: var(--text-2, var(--apple-text-muted, rgba(235, 235, 245, 0.7)));
    }

    /* Footnote role (13px Regular) */
    .footnote {
        font-size: 0.8125rem;
        line-height: 1.35;
        font-weight: 400;
        color: var(--text-2, var(--apple-text-muted, rgba(60, 60, 67, 0.6)));
    }

    .dark .footnote {
        color: var(--text-2, var(--apple-text-muted, rgba(235, 235, 245, 0.6)));
    }

    /* Caption role (11–12px) */
    .caption-sm {
        font-size: 0.75rem;
        line-height: 1.2;
        font-weight: 600;
        color: var(--text-2, var(--apple-text-muted, rgba(60, 60, 67, 0.6)));
    }

    .dark .caption-sm {
        color: var(--text-2, var(--apple-text-muted, rgba(235, 235, 245, 0.6)));
    }

    /* Pure Typographic Overline / Kicker */
    .overline,
    .kicker,
    .typographic-overline {
        font-size: 0.6875rem;
        /* 11px Mobile */
        line-height: 1.3;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-2, var(--apple-text-muted, rgba(60, 60, 67, 0.6)));
        display: block;
        margin-bottom: 0.25rem;
    }

    @media (min-width: 640px) {
        .overline,
        .kicker,
        .typographic-overline {
            font-size: 0.75rem;
            /* 12px Tablet & Desktop */
        }
    }

    .dark .overline,
    .dark .kicker,
    .dark .typographic-overline {
        color: var(--text-2, var(--apple-text-muted, rgba(235, 235, 245, 0.6)));
    }

    /* ==========================================================================
       3. TABULAR NUMERALS (FINANCIAL & QUANTITATIVE DATA)
       ========================================================================== */
    .tabular-nums,
    .num-tabular,
    .tnum {
        font-variant-numeric: tabular-nums;
        -moz-font-feature-settings: "tnum";
        -webkit-font-feature-settings: "tnum";
        font-feature-settings: "tnum";
    }

    /* ==========================================================================
       4. EDITORIAL & PROSE HEADINGS SPACING (.prose-headings / .article-body)
       ========================================================================== */
    .prose-headings h1,
    .article-body h1,
    .article-content h1,
    .markdown-body h1 {
        margin-top: 1.75rem;
        margin-bottom: 0.75rem;
    }

    .prose-headings h2,
    .article-body h2,
    .article-content h2,
    .markdown-body h2 {
        margin-top: 1.5rem;
        margin-bottom: 0.625rem;
    }

    .prose-headings h3,
    .article-body h3,
    .article-content h3,
    .markdown-body h3 {
        margin-top: 1.25rem;
        margin-bottom: 0.5rem;
    }

    .prose-headings h4,
    .article-body h4,
    .article-content h4,
    .markdown-body h4,
    .prose-headings h5,
    .article-body h5,
    .article-content h5,
    .markdown-body h5,
    .prose-headings h6,
    .article-body h6,
    .article-content h6,
    .markdown-body h6 {
        margin-top: 1rem;
        margin-bottom: 0.375rem;
    }

    .prose-headings>*:first-child,
    .article-body>*:first-child,
    .article-content>*:first-child,
    .markdown-body>*:first-child {
        margin-top: 0 !important;
    }

    /* ==========================================================================
       5. ANTI-OVERLAP & TEXT BALANCING UTILITIES
       ========================================================================== */
    .text-balance {
        text-wrap: balance;
    }

    .text-pretty {
        text-wrap: pretty;
    }

    .break-words {
        overflow-wrap: break-word;
        word-break: break-word;
    }

    /* Prevent awkward text collisions in flex rows */
    .text-container-safe {
        min-width: 0;
        flex: 1 1 0%;
        overflow-wrap: break-word;
    }

    /* Safe line-height for large titles */
    .leading-title {
        line-height: 1.22;
    }
</style>
