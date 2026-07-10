import { Link } from 'react-router-dom'
import { useParams } from 'react-router-dom'
import { motion } from 'framer-motion'
import { Helmet } from 'react-helmet-async'
import { useTranslation } from 'react-i18next'
import SEO from '../components/SEO'
import ShareButton from '../components/ShareButton'
import { usePost } from '../hooks/usePost'
import { usePosts } from '../hooks/usePosts'

const CATEGORY_KEYS = {
  '': 'blog.categoryAll',
  'care-guide': 'blog.categoryCareGuides',
  'style-tips': 'blog.categoryStyleTips',
  'leather-knowledge': 'blog.categoryLeatherKnowledge',
  'news': 'blog.categoryNews',
}

function Skeleton() {
  return (
    <div className="min-h-screen bg-dark pt-32 pb-24 px-6 lg:px-12 animate-pulse">
      <div className="max-w-3xl mx-auto">
        <div className="h-3 bg-dark-100 w-1/4 mb-6" />
        <div className="h-12 bg-dark-100 w-3/4 mb-4" />
        <div className="h-4 bg-dark-100 w-1/2 mb-10" />
        <div className="w-full bg-dark-100 mb-10" style={{ aspectRatio: '16/9' }} />
        {[1,2,3,4,5].map(i => <div key={i} className="h-3 bg-dark-100 w-full mb-3" />)}
      </div>
    </div>
  )
}

function RelatedBlogs({ currentPost }) {
  const { t, i18n } = useTranslation()
  const isAr = i18n.language?.startsWith('ar')
  const { posts: categoryPosts, loading: categoryLoading } = usePosts(
    currentPost.category ? { category: currentPost.category } : {}
  )
  const { posts: latestPosts, loading: latestLoading } = usePosts({})

  const related = categoryPosts
    .filter((item) => item.slug !== currentPost.slug)
    .slice(0, 3)

  const fallback = latestPosts
    .filter((item) => item.slug !== currentPost.slug && !related.some((rel) => rel.slug === item.slug))
    .slice(0, Math.max(0, 3 - related.length))

  const posts = [...related, ...fallback]
  const loading = categoryLoading || (related.length < 3 && latestLoading)

  if (loading || posts.length === 0) return null

  return (
    <section className="mt-16 pt-12 border-t border-gold/15">
      <div className="flex items-end justify-between gap-4 mb-8">
        <div>
          <p className="text-[10px] text-gold/50 tracking-[0.35em] uppercase mb-2">
            {isAr ? 'اقرأ أيضا' : 'Continue Reading'}
          </p>
          <h2 className="font-serif text-3xl md:text-4xl text-white font-light">
            {isAr ? 'مقالات ذات صلة' : 'Relevant Blogs'}
          </h2>
        </div>
        <Link to="/blog" className="hidden sm:inline text-[10px] text-white/30 hover:text-gold tracking-[0.25em] uppercase transition-colors duration-300">
          {t('blog.journal')} →
        </Link>
      </div>

      <div className="grid md:grid-cols-3 gap-5">
        {posts.map((item, index) => {
          const date = item.published_at
            ? new Date(item.published_at).toLocaleDateString(isAr ? 'ar-OM' : 'en-OM', { day: 'numeric', month: 'short', year: 'numeric' })
            : ''
          const category = CATEGORY_KEYS[item.category] ? t(CATEGORY_KEYS[item.category]) : item.category?.replace(/-/g, ' ')

          return (
            <motion.article
              key={item.slug}
              initial={{ opacity: 0, y: 16 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true, margin: '-80px' }}
              transition={{ duration: 0.35, delay: index * 0.06 }}
              className="group h-full"
            >
              <Link to={`/blog/${item.slug}`} className="flex h-full flex-col border border-white/8 hover:border-gold/30 bg-dark-100/35 hover:bg-dark-100/65 transition-colors duration-300">
                <div className="relative overflow-hidden bg-dark border-b border-white/8" style={{ aspectRatio: '16/10' }}>
                  {item.featured_image ? (
                    <img
                      src={item.featured_image}
                      alt={item.featured_image_alt || item.title}
                      className="absolute inset-0 w-full h-full object-contain group-hover:scale-105 transition-transform duration-700"
                    />
                  ) : (
                    <div className="absolute inset-0 flex items-center justify-center text-white/15 text-4xl">✍️</div>
                  )}
                  {category && (
                    <span className="absolute left-3 top-3 bg-dark/80 border border-gold/25 px-2 py-1 text-[8px] text-gold tracking-[0.18em] uppercase backdrop-blur-sm">
                      {category}
                    </span>
                  )}
                </div>

                <div className="flex flex-1 flex-col p-4">
                  <div className="flex flex-wrap items-center gap-2 text-[9px] text-white/30 tracking-[0.2em] uppercase mb-2">
                    {date && <span>{date}</span>}
                    {item.read_time && <><span>·</span><span>{item.read_time} {t('blog.minRead')}</span></>}
                  </div>
                  <h3 className="font-serif text-lg text-white font-light leading-snug group-hover:text-gold transition-colors duration-300">
                    {item.title}
                  </h3>
                  {item.excerpt && (
                    <p className="mt-2 text-sm text-white/40 leading-relaxed line-clamp-3">{item.excerpt}</p>
                  )}
                  <div className="mt-auto pt-4 text-[10px] text-gold tracking-[0.25em] uppercase">
                    {t('blog.readArticle')} →
                  </div>
                </div>
              </Link>
            </motion.article>
          )
        })}
      </div>
    </section>
  )
}

export default function BlogPostPage() {
  const { t, i18n } = useTranslation()
  const isAr = i18n.language?.startsWith('ar')
  const { slug } = useParams()
  const { post, loading, error } = usePost(slug)

  if (loading) return <Skeleton />

  if (error || !post) return (
    <div className="min-h-screen bg-dark flex items-center justify-center">
      <div className="text-center">
        <p className="text-5xl mb-6">📄</p>
        <p className="font-serif text-2xl text-white/40 font-light mb-6">{t('blog.notFound')}</p>
        <Link to="/blog" className="text-gold text-sm tracking-widest uppercase">← {t('blog.journal')}</Link>
      </div>
    </div>
  )

  const date = post.published_at
    ? new Date(post.published_at).toLocaleDateString(isAr ? 'ar-OM' : 'en-OM', { day: 'numeric', month: 'long', year: 'numeric' })
    : ''

  const categoryLabel = CATEGORY_KEYS[post.category] ? t(CATEGORY_KEYS[post.category]) : post.category?.replace(/-/g, ' ')

  const seoTitle = post.meta_title || post.title
  const seoDesc  = post.meta_description || post.excerpt || 'Read this article on the Artisan Leather blog.'

  const articleSchema = {
    '@context': 'https://schema.org',
    '@type': 'Article',
    headline: post.title,
    description: post.excerpt || seoDesc,
    image: post.featured_image || 'https://artisanleatherom.com/og-image.jpg',
    author: { '@type': 'Organization', name: post.author || 'Artisan Leather' },
    publisher: {
      '@type': 'Organization',
      name: 'Artisan Leather',
      logo: { '@type': 'ImageObject', url: 'https://artisanleatherom.com/logo.png' }
    },
    datePublished: post.published_at,
    mainEntityOfPage: `https://artisanleatherom.com/blog/${post.slug}`,
  }

  return (
    <div className="min-h-screen bg-dark pb-24">

      <SEO
        title={seoTitle}
        description={seoDesc}
        image={post.featured_image || undefined}
        url={`/blog/${post.slug}`}
        type="article"
      />
      <Helmet>
        <script type="application/ld+json">{JSON.stringify(articleSchema)}</script>
        {/* Breadcrumb */}
        <script type="application/ld+json">{JSON.stringify({
          '@context': 'https://schema.org',
          '@type': 'BreadcrumbList',
          itemListElement: [
            { '@type': 'ListItem', position: 1, name: 'Home',    item: 'https://artisanleatherom.com' },
            { '@type': 'ListItem', position: 2, name: 'Journal', item: 'https://artisanleatherom.com/blog' },
            { '@type': 'ListItem', position: 3, name: post.title },
          ],
        })}</script>
      </Helmet>

      {/* Hero / Header */}
      <section className="relative pt-36 pb-12 px-6 lg:px-12 border-b border-gold/10">
        <div className="absolute inset-0 bg-gradient-to-b from-dark-100 to-dark" />
        <div className="relative max-w-3xl mx-auto">

          {/* Breadcrumb navigation */}
          <nav className="flex items-center gap-2 text-[10px] tracking-[0.3em] uppercase mb-8">
            <Link to="/" className="text-white/25 hover:text-gold transition-colors duration-200">{t('blog.home')}</Link>
            <span className="text-white/15">›</span>
            <Link to="/blog" className="text-white/25 hover:text-gold transition-colors duration-200">{t('blog.journal')}</Link>
            <span className="text-white/15">›</span>
            <span className="text-gold/60 truncate max-w-[200px]">{categoryLabel}</span>
          </nav>

          <motion.h1
            initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}
            className="font-serif text-4xl md:text-5xl text-white font-light leading-tight mb-6">
            {post.title}
          </motion.h1>

          <div className="flex items-center justify-between gap-4 flex-wrap">
            <div className="flex items-center gap-4 text-[11px] text-white/30 tracking-wider">
              <span>{post.author}</span>
              <span>·</span>
              <span>{date}</span>
              <span>·</span>
              <span>{post.read_time} {t('blog.minRead')}</span>
              {post.tags?.length > 0 && (
                <>
                  <span>·</span>
                  <div className="flex gap-2">
                    {post.tags.slice(0, 3).map(tag => (
                      <span key={tag} className="border border-white/10 px-2 py-0.5 text-[9px]">{tag}</span>
                    ))}
                  </div>
                </>
              )}
            </div>
            <ShareButton url={typeof window !== 'undefined' ? window.location.href : ''} title={post.title} menuAlign="left" />
          </div>
        </div>
      </section>

      {/* Featured image */}
      {post.featured_image && (
        <div className="max-w-4xl mx-auto px-6 lg:px-12 mt-12">
          <div className="relative w-full bg-dark-100" style={{ aspectRatio: '16/9' }}>
            <img
              src={post.featured_image}
              alt={post.featured_image_alt || post.title}
              loading="eager"
              decoding="async"
              fetchPriority="high"
              className="absolute inset-0 w-full h-full object-contain"
            />
          </div>
        </div>
      )}

      {/* Article content */}
      <article className="max-w-3xl mx-auto px-6 lg:px-12 mt-12">
        <div
          className="prose prose-invert prose-lg max-w-none
            prose-headings:font-serif prose-headings:text-white prose-headings:tracking-wide
            prose-h2:text-2xl md:prose-h2:text-3xl prose-h2:font-medium prose-h2:mt-14 prose-h2:mb-5 prose-h2:pb-4 prose-h2:border-b prose-h2:border-gold/20 first:prose-h2:mt-0
            prose-h3:text-xl md:prose-h3:text-2xl prose-h3:font-medium prose-h3:mt-10 prose-h3:mb-3
            prose-p:text-white/65 prose-p:leading-relaxed prose-p:mb-6
            prose-a:text-gold prose-a:no-underline hover:prose-a:underline
            prose-strong:text-white
            prose-li:text-white/65
            prose-blockquote:border-gold/40 prose-blockquote:text-white/50 prose-blockquote:italic
            prose-hr:border-gold/20"
          dangerouslySetInnerHTML={{ __html: post.content }}
        />

        {/* Tags */}
        {post.tags?.length > 0 && (
          <div className="mt-12 pt-8 border-t border-white/8 flex flex-wrap gap-2">
            {post.tags.map(tag => (
              <span key={tag}
                className="border border-white/10 text-white/40 px-3 py-1 text-[10px] tracking-[0.2em] uppercase">
                {tag}
              </span>
            ))}
          </div>
        )}

        <RelatedBlogs currentPost={post} />

        {/* Back to blog */}
        <div className="mt-12 pt-8 border-t border-white/8">
          <Link
            to="/blog"
            className="inline-flex items-center gap-3 text-white/40 hover:text-gold transition-colors duration-300 text-[10px] tracking-[0.3em] uppercase group">
            <span className="group-hover:-translate-x-1 transition-transform duration-300">←</span>
            {t('blog.backToJournal')}
          </Link>
        </div>
      </article>

    </div>
  )
}
