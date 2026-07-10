import { useState } from 'react'
import { Link } from 'react-router-dom'
import { motion } from 'framer-motion'
import { useTranslation } from 'react-i18next'
import SEO from '../components/SEO'
import { usePosts } from '../hooks/usePosts'

function getCategories(t) {
  return [
    { id: '',                  label: t('blog.categoryAll'),             icon: '📋' },
    { id: 'care-guide',       label: t('blog.categoryCareGuides'),        icon: '🧴' },
    { id: 'style-tips',       label: t('blog.categoryStyleTips'),         icon: '👔' },
    { id: 'leather-knowledge', label: t('blog.categoryLeatherKnowledge'), icon: '📖' },
    { id: 'news',             label: t('blog.categoryNews'),              icon: '📰' },
  ]
}

function PostCard({ post, index }) {
  const { t, i18n } = useTranslation()
  const isAr = i18n.language?.startsWith('ar')
  const date = post.published_at
    ? new Date(post.published_at).toLocaleDateString(isAr ? 'ar-OM' : 'en-OM', { day: 'numeric', month: 'long', year: 'numeric' })
    : ''

  const catLabel = getCategories(t).find(c => c.id === post.category)

  return (
    <motion.article
      initial={{ opacity: 0, y: 28 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.55, delay: (index % 6) * 0.07 }}
      className="group flex flex-col"
    >
      <Link to={`/blog/${post.slug}`} className="flex flex-col h-full">

        {/* Cover image */}
        <div className="relative overflow-hidden bg-dark-100" style={{ aspectRatio: '16/9' }}>
          {post.featured_image ? (
            <img
              src={post.featured_image}
              alt={post.featured_image_alt || post.title}
              loading={index < 2 ? 'eager' : 'lazy'}
              decoding="async"
              className="absolute inset-0 w-full h-full object-contain group-hover:scale-105 transition-transform duration-700"
            />
          ) : (
            <div className="absolute inset-0 bg-gradient-to-br from-dark-100 to-dark-200 flex items-center justify-center">
              <span className="text-6xl opacity-20">✍️</span>
            </div>
          )}

          {/* Category badge */}
          {catLabel && (
            <div className="absolute top-3 left-3">
              <span className="bg-gold text-dark text-[9px] tracking-[0.2em] uppercase px-2.5 py-1 font-bold">
                {catLabel.icon} {catLabel.label}
              </span>
            </div>
          )}

          {/* Hover overlay */}
          <div className="absolute inset-0 bg-dark/50 opacity-0 group-hover:opacity-100 transition-opacity duration-400 flex items-center justify-center">
            <span className="border border-gold text-gold px-6 py-2 text-[10px] tracking-[0.3em] uppercase">
              {t('blog.readArticle')} →
            </span>
          </div>
        </div>

        {/* Text content */}
        <div className="mt-4 flex flex-col flex-1">
          <div className="flex items-center gap-3 text-[10px] text-white/30 tracking-widest uppercase mb-2">
            <span>{date}</span>
            {post.read_time && <><span>·</span><span>{post.read_time} {t('blog.minRead')}</span></>}
          </div>
          <h2 className="font-serif text-xl text-white font-light group-hover:text-gold transition-colors duration-300 leading-snug mb-2">
            {post.title}
          </h2>
          {post.excerpt && (
            <p className="text-white/40 text-sm leading-relaxed line-clamp-3 flex-1">{post.excerpt}</p>
          )}
          <div className="mt-4 flex items-center gap-2 text-gold text-[10px] tracking-[0.25em] uppercase">
            <span>{t('blog.readArticle')}</span>
            <span className="group-hover:translate-x-1 transition-transform duration-300">→</span>
          </div>
        </div>
      </Link>
    </motion.article>
  )
}

function PostSkeleton() {
  return (
    <div className="animate-pulse">
      <div className="bg-dark-100 w-full" style={{ aspectRatio: '16/9' }} />
      <div className="mt-4 space-y-3">
        <div className="h-2 bg-dark-100 w-1/4 rounded" />
        <div className="h-5 bg-dark-100 w-full rounded" />
        <div className="h-3 bg-dark-100 w-5/6 rounded" />
        <div className="h-3 bg-dark-100 w-3/4 rounded" />
      </div>
    </div>
  )
}

export default function BlogPage() {
  const { t } = useTranslation()
  const [activeCategory, setActiveCategory] = useState('')
  const { posts, loading } = usePosts(activeCategory ? { category: activeCategory } : {})
  const CATEGORIES = getCategories(t)
  const activeCat = CATEGORIES.find(c => c.id === activeCategory)

  return (
    <div className="min-h-screen bg-dark pb-24">
      <SEO
        title="The Leather Journal — Care Guides & Style Tips"
        description="Expert leather care guides, style tips, and stories from the artisans at Artisan Leather, Muscat Oman."
        url="/blog"
      />

      {/* ── Page Hero ───────────────────────────────────────────────────── */}
      <section className="relative pt-36 pb-16 px-6 lg:px-12 border-b border-gold/10 overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-b from-dark-100 to-dark" />
        <div className="absolute left-1/2 top-0 bottom-0 w-px bg-gradient-to-b from-transparent via-gold/8 to-transparent" />

        <div className="relative max-w-7xl mx-auto">

          {/* Breadcrumb navigation */}
          <nav className="flex items-center gap-2 text-[10px] tracking-[0.3em] uppercase text-white/30 mb-8">
            <Link to="/" className="hover:text-gold transition-colors duration-200">{t('blog.home')}</Link>
            <span>›</span>
            <span className="text-gold/70">{t('blog.journal')}</span>
          </nav>

          <div className="max-w-2xl">
            <motion.p initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }}
              className="text-gold/60 tracking-[0.5em] uppercase text-[10px] mb-4">
              {t('blog.eyebrow')}
            </motion.p>
            <motion.h1 initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.1 }}
              className="font-serif text-5xl md:text-6xl text-white font-light mb-5">
              {t('blog.title')}
            </motion.h1>
            <motion.p initial={{ opacity: 0 }} animate={{ opacity: 1 }}
              transition={{ delay: 0.2 }}
              className="text-white/40 text-base font-light leading-relaxed">
              {t('blog.subtitle')}
            </motion.p>
          </div>

          {/* Stats row */}
          <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }}
            transition={{ delay: 0.3 }}
            className="flex items-center gap-6 mt-8 text-[10px] text-white/25 tracking-widest uppercase">
            <span>{posts.length || 10} {t('blog.articles')}</span>
            <span>·</span>
            <span>4 {t('blog.categories')}</span>
            <span>·</span>
            <span>{t('blog.expertAuthors')}</span>
          </motion.div>
        </div>
      </section>

      {/* ── Category Navigation ──────────────────────────────────────────── */}
      <nav className="sticky top-16 z-30 bg-dark/95 backdrop-blur-sm border-b border-gold/10 px-6 lg:px-12">
        <div className="max-w-7xl mx-auto">
          <div className="flex items-center gap-1 overflow-x-auto py-3 scrollbar-none">
            {CATEGORIES.map(cat => (
              <button
                key={cat.id}
                onClick={() => setActiveCategory(cat.id)}
                className={`flex-shrink-0 flex items-center gap-1.5 px-4 py-2 text-xs tracking-[0.12em] uppercase transition-all duration-200 rounded-sm
                  ${activeCategory === cat.id
                    ? 'bg-gold text-dark font-bold'
                    : 'text-white/40 hover:text-white/80 hover:bg-white/5'
                  }`}
              >
                <span>{cat.icon}</span>
                <span>{cat.label}</span>
              </button>
            ))}

            {/* Article count */}
            {!loading && (
              <span className="ml-auto flex-shrink-0 text-[10px] text-white/20 tracking-widest pr-2">
                {posts.length} {posts.length === 1 ? t('blog.article') : t('blog.articlesCount')}
                {activeCat?.id ? ` ${t('blog.in')} ${activeCat.label}` : ''}
              </span>
            )}
          </div>
        </div>
      </nav>

      {/* ── Articles Grid ─────────────────────────────────────────────────── */}
      <section className="max-w-7xl mx-auto px-6 lg:px-12 py-14">

        {/* Active category heading */}
        {activeCat?.id && (
          <div className="flex items-center justify-between mb-10">
            <div>
              <p className="text-[10px] text-white/30 tracking-[0.4em] uppercase mb-1">{t('blog.filteredBy')}</p>
              <h2 className="font-serif text-2xl text-white font-light">
                {activeCat.icon} {activeCat.label}
              </h2>
            </div>
            <button
              onClick={() => setActiveCategory('')}
              className="text-[10px] text-white/30 hover:text-gold tracking-[0.3em] uppercase transition-colors duration-200 border border-white/10 hover:border-gold/30 px-3 py-1.5"
            >
              ✕ {t('blog.clear')}
            </button>
          </div>
        )}

        {loading ? (
          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-10">
            {[1,2,3,4,5,6].map(i => <PostSkeleton key={i} />)}
          </div>
        ) : posts.length === 0 ? (
          <div className="text-center py-24">
            <p className="text-5xl mb-6">✍️</p>
            <p className="font-serif text-2xl text-white/40 font-light mb-2">{t('blog.noArticles')}</p>
            <button onClick={() => setActiveCategory('')}
              className="mt-6 text-gold text-xs tracking-[0.3em] uppercase hover:underline">
              ← {t('blog.viewAllArticles')}
            </button>
          </div>
        ) : (
          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-10 lg:gap-12">
            {posts.map((post, i) => (
              <PostCard key={post.id} post={post} index={i} />
            ))}
          </div>
        )}

      </section>

      {/* ── Bottom CTA ────────────────────────────────────────────────────── */}
      <section className="max-w-7xl mx-auto px-6 lg:px-12 pb-4">
        <div className="border-t border-gold/10 pt-12 flex flex-col sm:flex-row items-center justify-between gap-6">
          <div>
            <p className="font-serif text-xl text-white font-light mb-1">{t('blog.ctaTitle')}</p>
            <p className="text-white/30 text-sm">{t('blog.ctaSubtitle')}</p>
          </div>
          <Link
            to="/collections"
            className="flex-shrink-0 px-8 py-3 bg-gold text-dark text-[10px] tracking-[0.35em] uppercase font-bold hover:bg-amber-400 transition-colors duration-300"
          >
            {t('common.shopCollection')} →
          </Link>
        </div>
      </section>

    </div>
  )
}
