<!-- Generated with 🧡 at typo3-badges.dev -->
![TYPO3 extension](https://typo3-badges.dev/badge/turnstile/extension/shields.svg)
![Total downloads](https://typo3-badges.dev/badge/turnstile/downloads/shields.svg)
![Stability](https://typo3-badges.dev/badge/turnstile/stability/shields.svg)
[![ci](https://github.com/tritum/turnstile/actions/workflows/ci.yml/badge.svg)](https://github.com/tritum/turnstile/actions/workflows/ci.yml)
[![phpstan](https://img.shields.io/badge/PHPStan-lvl%20max-blueviolet)](https://phpstan.org/)
![TYPO3 versions](https://typo3-badges.dev/badge/turnstile/typo3/shields.svg)
![Latest version](https://typo3-badges.dev/badge/turnstile/version/shields.svg)

# TYPO3 Extension to add Turnstile to EXT:form

This TYPO3 extension provides Turnstile integration for the TYPO3 Form Framework (EXT:form).

For more information check the [Turnstile website](https://www.cloudflare.com/products/turnstile/).

# Quick Setup

1. Install the extension and activate it.
2. Add the static TypoScript configuration to your TypoScript template.
3. Get a `Turnstile` `site_key` and a `privateKey`. Set the TypoScript Constants appropriately.
4. Add a `Turnstile` element to a form.

## Get Turnstile keys for your website

You need two API keys from Cloudflare. Retrieving those keys is free of
charge. Open your [Cloudflare dashboard](https://dash.cloudflare.com/?to=/:account/turnstile).
Add a new entry for your website. In order to do so, provide a name of the website
and the domain. This also works for multiple environments, just provide the domain
like `tritum.de`. Any subdomain or local domain will also be supported.

Furthermore, you can choose between three different modes for the challenge.
For more information see: https://developers.cloudflare.com/turnstile/reference/widget-types/.


## TypoScript Constants

Set the following TypoScript Constants:

```typo3_typoscript
plugin.tx_turnstile {
    settings {
        # Get API keys at https://dash.cloudflare.com/?to=/:account/turnstile
        siteKey = <your-site-key>
        privateKey = <your-private-key>
    }
}
```

## Environment variables

As an alternative to the TypoScript configuration, you can also use environment variables:

* `TURNSTILE_SITE_KEY`
* `TURNSTILE_PRIVATE_KEY`

# Content Security Policy

If you are using CSP, make sure to adjust them accordingly:

* script-src should include `https://challenges.cloudflare.com`
* frame-src should include `https://challenges.cloudflare.com`
* style-src should include `https://challenges.cloudflare.com`

# Privacy

Make sure to inform your users of your usage of Turnstile and what that means.

For more information see: https://www.cloudflare.com/de-de/trust-hub/gdpr/.

# Help & Support

Please use GitHub for issue tracking. For more information see: https://github.com/tritum/turnstile/issues.
