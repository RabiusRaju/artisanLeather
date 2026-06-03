import { Link } from 'react-router-dom'
import { useParams } from 'react-router-dom'
import { motion } from 'framer-motion'
import { Helmet } from 'react-helmet-async'
import SEO from '../components/SEO'
import { usePost } from '../hooks/usePost'

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

export default function BlogPostPage() {
  const { slug } = useParams()
  const { post, loading, error } = usePost(slug)

  if (loading) return <Skeleton />

  if (error || !post) return (
    <div className="min-h-screen bg-dark flex items-center justify-center">
      <div className="text-center">
        <p className="text-5xl mb-6">📄</p>
        <p className="font-serif text-2xl text-white/40 font-light mb-6">Article not found</p>
        <Link to="/blog" className="text-gold text-sm tracking-widest uppercase">← Back to Journal</Link>
      </div>
    </div>
  )

  const date = post.published_at
    ? new Date(post.published_at).toLocaleDateString('en-OM', { day: 'numeric', month: 'long', year: 'numeric' })
    : ''

  const seoTitle = post.meta_title || `${post.title} | Artisan Leather Blog`
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
            <Link to="/" className="text-white/25 hover:text-gold transition-colors duration-200">Home</Link>
            <span className="text-white/15">›</span>
            <Link to="/blog" className="text-white/25 hover:text-gold transition-colors duration-200">Journal</Link>
            <span className="text-white/15">›</span>
            <span className="text-gold/60 truncate max-w-[200px]">{post.category?.replace(/-/g, ' ')}</span>
          </nav>

          <motion.h1
            initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}
            className="font-serif text-4xl md:text-5xl text-white font-light leading-tight mb-6">
            {post.title}
          </motion.h1>

          <div className="flex items-center gap-4 text-[11px] text-white/30 tracking-wider">
            <span>{post.author}</span>
            <span>·</span>
            <span>{date}</span>
            <span>·</span>
            <span>{post.read_time} min read</span>
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
        </div>
      </section>

      {/* Featured image */}
      {post.featured_image && (
        <div className="max-w-4xl mx-auto px-6 lg:px-12 mt-12">
          <img
            src={post.featured_image}
            alt={post.title}
            className="w-full object-cover"
            style={{ aspectRatio: '16/9' }}
          />
        </div>
      )}

      {/* Article content */}
      <article className="max-w-3xl mx-auto px-6 lg:px-12 mt-12">
        <div
          className="prose prose-invert prose-lg max-w-none
            prose-headings:font-serif prose-headings:font-light prose-headings:text-white
            prose-p:text-white/65 prose-p:leading-relaxed
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

        {/* Back to blog */}
        <div className="mt-12 pt-8 border-t border-white/8">
          <Link
            to="/blog"
            className="inline-flex items-center gap-3 text-white/40 hover:text-gold transition-colors duration-300 text-[10px] tracking-[0.3em] uppercase group">
            <span className="group-hover:-translate-x-1 transition-transform duration-300">←</span>
            Back to the Journal
          </Link>
        </div>
      </article>

    </div>
  )
}
