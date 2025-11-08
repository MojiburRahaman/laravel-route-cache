# Security Policy

## Supported Versions

We actively maintain the latest `main` branch and the most recent tagged release. If you depend on an older version, we recommend upgrading to receive fixes.

| Version | Supported |
|---------|-----------|
| main    | ✅        |
| latest release | ✅ |
| older releases | ⚠️ – best effort only |

## Reporting a Vulnerability

If you discover a potential security issue:

1. **Do not open a public issue.**
2. Email the maintainer at `contact@mojiburrahaman.dev` with:
   - A description of the vulnerability.
   - Steps to reproduce or proof of concept.
   - Any suggested remediation, if available.
3. You will receive an acknowledgment within 3 business days.

We aim to provide a status update within 7 business days and will coordinate disclosure once a fix is ready. If we cannot reproduce the issue, we may ask for additional detail.

## Security Best Practices

- Run on supported PHP versions (7.4 or later).
- Keep dependencies updated and follow Laravel’s own security guidance.
- Configure Redis with authentication/firewall rules when exposed outside trusted networks.

Thank you for helping keep the community secure. If you have questions, reach out at `contact@mojiburrahaman.dev`.

