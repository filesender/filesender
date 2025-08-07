# Security Policy

## Supported Versions

We provide security updates for the following versions:

| Version | Supported          |
| ------- | ------------------ |
| 3.x     | ⚠️ Not supported (yet) |
| 2.x     | ✅ Supported       |
| < 2.x   | ⚠️ Not supported   |

We do not provide security patches to any releases below 2.x and expect to start supporting 3.x as soon as the first release is made.
We encourage all users to upgrade to the latest stable release.

---

## Reporting a Vulnerability

If you discover a security vulnerability in FileSender, please report it responsibly.

- **Do not** create public issues or pull requests that disclose security issues.
- Please email us at: filesender.security@commonsconservancy.org, preferably using GPG encryption. Alternatively, you can contact us via email to request another secure channel for transmitting the passphrase, such as Signal.

Include:
- A clear description of the issue and potential impact.
- Steps to reproduce or a proof-of-concept.
- Any relevant logs or context.

We will acknowledge your report within **5 business days**, and work with you on a resolution.

---

## Disclosure Process

Our disclosure process is based on responsible coordination with reporters:

1. **Initial report:**  
   You privately share details of the vulnerability with us via `filesender.security@commonsconservancy.org`.

2. **Acknowledgement:**  
   We will acknowledge your report within **5 business days**, and may ask for additional details.

3. **Investigation & fix:**  
   - We investigate and develop a fix or mitigation.
   - We aim to complete this within **90 days**, depending on complexity and severity.
   - We coordinate with you to confirm the fix actually resolves the vulnerability in your inital report.

4. **Coordinated release:**  
   - We issue an advanced warning to parties contributing to FileSender
   - We prepare a new release with the fix.
   - We issue a security advisory and CVE if applicable.
   - We credit the reporter unless anonymity is requested.

5. **Public disclosure:**  
   Once the patch is available, we disclose the issue publicly, including remediation steps.

We strive to keep you informed throughout the process and welcome coordination on disclosure timing to protect users.

---

## Security Practices

- FileSender regularly publishes security advisories on [https://filesender.org](https://filesender.org).
- Always run the latest stable release to ensure you have current security patches.
- For high-sensitivity data, enable client-side encryption so that even the server cannot read file contents.

More on our security approach:  
[https://docs.filesender.org/filesender/v3.0/security/](https://docs.filesender.org/filesender/v3.0/security/)

---

## Questions?

For general security questions (not vulnerabilities), join our dev mailinglist: filesender-dev@filesender.org
