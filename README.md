# TYPO3 Extension to add turnstile to EXT:form.

[![ci](https://github.com/tritum/turnstile/actions/workflows/ci.yml/badge.svg)](https://github.com/tritum/turnstile/actions/workflows/ci.yml)
[![phpstan](https://img.shields.io/badge/PHPStan-lvl%20max-blueviolet)](https://phpstan.org/)

Provides turnstile integration for TYPO3 EXT:form.

For more information, see [the turnstile website](https://www.cloudflare.com/products/turnstile/).

## Quick Setup

- Install the extension and activate it
- Include the static template in TypoScript
- Add a `turnstile` element to a form

### TypoScript Constants

Set the following typoscript constants:

```typo3_typoscript
plugin.tx_turnstile {
  settings {
    # Get API keys at https://dash.cloudflare.com/?to=/:account/turnstile
    siteKey = <your-site-key>
    privateKey = <your-private-key>
  }
}

}
```

### Environment variables

As an alternative to the TypoScript configuration, you can also use environment variables:

* `TURNSTILE_SITE_KEY`
* `TURNSTILE_PRIVATE_KEY`

### Content Security Policy

If you are using CSP, make sure to adjust them accordingly:

* script-src should include `https://challenges.cloudflare.com`
* frame-src should include `https://challenges.cloudflare.com`
* style-src should include `https://challenges.cloudflare.com`

## Privacy

Make sure to inform your users of your usage of turnstile and what that means.

For more info see: https://www.cloudflare.com/de-de/trust-hub/gdpr/

### Help & Support

* Issues: https://github.com/tritum/turnstile/issues