/**
 * Panel side of the plugin.
 *
 * k-login-view is wrapped rather than copied: the inherited render function
 * builds the view as Kirby ships it, and the link list is appended to the
 * slot of its root component (k-panel-outside). Kirby turns a string in
 * `extends` into a Vue constructor, hence `.options.render`.
 *
 * k-login-view covers the password, code, 2FA and reset forms, so the links
 * appear in every state of the login.
 */
panel.plugin("kdjfs/login-legal-links", {
    components: {
        "k-login-view": {
            extends: "k-login-view",
            props: {
                legalLinks: { type: Array, default: () => [] },
                legalLinksNewTab: { type: Boolean, default: false },
                legalLinksWarnings: { type: Array, default: () => [] }
            },
            created() {
                // Only filled in debug mode, see plugin/areas.php
                for (const warning of this.legalLinksWarnings) {
                    console.warn("login-legal-links: " + warning);
                }
            },
            methods: {
                renderLegalLinks(h) {
                    const linkAttributes = this.legalLinksNewTab
                        ? { target: "_blank", rel: "noopener" }
                        : {};

                    const items = this.legalLinks.map((legalLink) =>
                        h("li", [
                            h("a", { attrs: { href: legalLink.url, ...linkAttributes } }, legalLink.label)
                        ])
                    );

                    return h(
                        "nav",
                        {
                            class: "k-login-legal-links",
                            attrs: { "aria-label": this.$t("kdjfs.login-legal-links.nav") }
                        },
                        [h("ul", items)]
                    );
                }
            },
            render(h) {
                const originalView = this.$options.extends.options.render.call(this, h);
                const slotChildren = originalView?.componentOptions?.children;

                // Nothing to show, or unknown structure after a Kirby update: show the login as it is
                if (this.legalLinks.length === 0 || Array.isArray(slotChildren) === false) {
                    return originalView;
                }

                slotChildren.push(this.renderLegalLinks(h));

                return originalView;
            }
        }
    },
    fields: {
        "login-legal-links": {
            extends: "k-structure-field"
        }
    }
});
