import { useSetting } from '../hooks/useSettings'
import { Link } from 'react-router-dom'
import { FaInstagram, FaWhatsapp, FaFacebook, FaGoogle } from 'react-icons/fa'

const linkGroups = [
  {
    title: 'Collections',
    links: [
      { label: 'Wallets', to: '/collections/wallets' },
      { label: 'Bags', to: '/collections/bags' },
      { label: 'Belts', to: '/collections/belts' },
      { label: 'Accessories', to: '/collections/accessories' },
    ],
  },
  {
    title: 'Company',
    links: [
      { label: 'Our Story', to: '/about' },
      { label: 'Craftsmanship', to: '/about#craftsmanship' },
      { label: 'Sustainability', to: '/about#sustainability' },
      { label: 'Contact', to: '/contact' },
    ],
  },
]

export default function Footer() {
  const waNumber  = useSetting('business.whatsapp', '96812345678').replace(/[^0-9]/g, '')
  const instagram = useSetting('social.instagram', '')
  const facebook  = useSetting('social.facebook', '')
  const email     = useSetting('business.email', 'info@artisanleatherom.com')
  const tagline       = useSetting('footer.tagline',        'Premium handcrafted leather goods. Made in Oman. Delivered across the GCC.')
  const copyright     = useSetting('footer.copyright',      '© 2025 Artisan Leather · artisanleatherom.com · All rights reserved')
  const googleBusiness = useSetting('seo.google_business',  '')

  return (
    <footer className="dark-section bg-dark border-t border-gold/10 pt-16 pb-10 px-6 lg:px-12" style={{ backgroundColor: '#120D05' }}>
      <div className="max-w-7xl mx-auto">
        <div className="grid md:grid-cols-4 gap-12 mb-16">
          {/* Brand column */}
          <div className="md:col-span-1">
            <div className="flex items-center gap-3 mb-6">
              <img
                src="/logo-icon.png"
                alt="Artisan Leather"
                className="h-10 w-10 object-contain"
              />
              <div>
                <div className="font-serif text-sm tracking-[0.2em] text-gold uppercase leading-none">
                  Artisan
                </div>
                <div className="font-serif text-xs tracking-[0.35em] text-gold/60 uppercase mt-0.5">
                  Leather
                </div>
              </div>
            </div>
            <p className="text-white/35 text-sm leading-relaxed font-light max-w-48">
              {tagline}
            </p>
            <div className="flex gap-4 mt-7">
              {instagram && (
                <a
                  href={instagram}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="text-white/25 hover:text-gold transition-colors duration-300"
                  aria-label="Instagram"
                >
                  <FaInstagram size={17} />
                </a>
              )}
              <a
                href={`https://wa.me/${waNumber}`}
                target="_blank"
                rel="noopener noreferrer"
                className="text-white/25 hover:text-gold transition-colors duration-300"
                aria-label="WhatsApp"
              >
                <FaWhatsapp size={17} />
              </a>
              {facebook && (
                <a
                  href={facebook}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="text-white/25 hover:text-gold transition-colors duration-300"
                  aria-label="Facebook"
                >
                  <FaFacebook size={17} />
                </a>
              )}
              {googleBusiness && (
                <a
                  href={googleBusiness}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="text-white/25 hover:text-gold transition-colors duration-300"
                  aria-label="Google Business"
                >
                  <FaGoogle size={17} />
                </a>
              )}
            </div>
          </div>

          {/* Link columns */}
          {linkGroups.map((group) => (
            <div key={group.title}>
              <h4 className="text-white text-[10px] tracking-[0.35em] uppercase mb-7">
                {group.title}
              </h4>
              <ul className="space-y-3.5">
                {group.links.map((link) => (
                  <li key={link.label}>
                    <Link
                      to={link.to}
                      className="text-white/35 hover:text-gold text-sm font-light transition-colors duration-300"
                    >
                      {link.label}
                    </Link>
                  </li>
                ))}
              </ul>
            </div>
          ))}

          {/* Contact column */}
          <div>
            <h4 className="text-white text-[10px] tracking-[0.35em] uppercase mb-7">Contact</h4>
            <ul className="space-y-3.5 text-white/35 text-sm font-light">
              <li>Muscat, Sultanate of Oman</li>
              <li>
                <a
                  href={`https://wa.me/${waNumber}`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="hover:text-gold transition-colors duration-300"
                >
                  +{waNumber}
                </a>
              </li>
              <li>
                <a
                  href={`mailto:${email}`}
                  className="hover:text-gold transition-colors duration-300"
                >
                  {email}
                </a>
              </li>
              <li className="pt-3">
                <div className="flex gap-2">
                  <button className="border border-white/15 hover:border-gold/40 text-white/40 hover:text-gold px-3 py-1 text-[10px] tracking-wider transition-all duration-300">
                    EN
                  </button>
                  <button className="border border-white/15 hover:border-gold/40 text-white/40 hover:text-gold px-3 py-1 text-[10px] tracking-wider transition-all duration-300">
                    عربي
                  </button>
                </div>
              </li>
            </ul>
          </div>
        </div>

        {/* Bottom bar */}
        <div className="border-t border-white/5 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
          <p className="text-white/20 text-xs tracking-wider">
            {copyright}
          </p>
          <div className="flex gap-6">
            <Link to="/privacy" className="text-white/20 hover:text-gold/60 text-xs transition-colors">
              Privacy Policy
            </Link>
            <Link to="/terms" className="text-white/20 hover:text-gold/60 text-xs transition-colors">
              Terms of Service
            </Link>
          </div>
        </div>
      </div>
    </footer>
  )
}
