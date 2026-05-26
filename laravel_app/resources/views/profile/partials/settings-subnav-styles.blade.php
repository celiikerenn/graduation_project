<style>
    .settings-subnav {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin: 0.75rem 0 1.25rem;
        padding: 0.35rem;
        background: var(--surface2);
        border: 1px solid var(--border2);
        border-radius: 12px;
        width: fit-content;
        max-width: 100%;
    }
    .settings-subnav__link {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        border-radius: 9px;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--txt2);
        text-decoration: none;
        transition: background 0.15s ease, color 0.15s ease;
    }
    .settings-subnav__link:hover {
        color: var(--txt);
        background: rgba(37, 99, 235, 0.06);
    }
    .settings-subnav__link--active {
        color: var(--acc);
        background: var(--surface);
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
    }
</style>
