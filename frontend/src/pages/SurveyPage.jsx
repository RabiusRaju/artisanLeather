import { useState, useEffect } from 'react'
import { useParams, Link } from 'react-router-dom'
import { motion, AnimatePresence } from 'framer-motion'
import SEO from '../components/SEO'
import { fetchSurvey, submitSurvey } from '../services/api'

// ── Individual Question Components ────────────────────────────────────────────

function SingleChoice({ question, value, onChange }) {
  const opts = question.options || []
  return (
    <div className="space-y-3">
      {opts.map((opt, i) => (
        <label key={i} className="flex items-center gap-3 cursor-pointer group">
          <div className={`w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all duration-200
            ${value === opt ? 'border-gold bg-gold' : 'border-white/20 group-hover:border-gold/50'}`}>
            {value === opt && <div className="w-2 h-2 rounded-full bg-dark" />}
          </div>
          <input type="radio" className="hidden" value={opt} checked={value === opt}
            onChange={() => onChange(opt)} />
          <span className={`text-sm transition-colors duration-200 ${value === opt ? 'text-gold' : 'text-white/70 group-hover:text-white'}`}>
            {opt}
          </span>
        </label>
      ))}
    </div>
  )
}

function MultipleChoice({ question, value = [], onChange }) {
  const opts = question.options || []
  const toggle = (opt) => {
    const current = Array.isArray(value) ? value : []
    onChange(current.includes(opt) ? current.filter(v => v !== opt) : [...current, opt])
  }
  return (
    <div className="space-y-3">
      {opts.map((opt, i) => {
        const checked = Array.isArray(value) && value.includes(opt)
        return (
          <label key={i} className="flex items-center gap-3 cursor-pointer group">
            <div className={`w-5 h-5 border-2 flex items-center justify-center transition-all duration-200
              ${checked ? 'border-gold bg-gold' : 'border-white/20 group-hover:border-gold/50'}`}>
              {checked && <span className="text-dark text-xs font-bold">✓</span>}
            </div>
            <input type="checkbox" className="hidden" checked={checked} onChange={() => toggle(opt)} />
            <span className={`text-sm transition-colors duration-200 ${checked ? 'text-gold' : 'text-white/70 group-hover:text-white'}`}>
              {opt}
            </span>
          </label>
        )
      })}
    </div>
  )
}

function Rating({ question, value, onChange }) {
  const [hovered, setHovered] = useState(null)
  const max = 5
  return (
    <div className="flex gap-3">
      {Array.from({ length: max }, (_, i) => i + 1).map(star => (
        <button key={star} type="button"
          onMouseEnter={() => setHovered(star)}
          onMouseLeave={() => setHovered(null)}
          onClick={() => onChange(star)}
          className="text-3xl transition-all duration-150 hover:scale-110">
          {star <= (hovered ?? value ?? 0) ? '⭐' : '☆'}
        </button>
      ))}
      {value && <span className="text-white/40 text-sm self-center ml-2">{value} / 5</span>}
    </div>
  )
}

function NPS({ question, value, onChange }) {
  const [hovered, setHovered] = useState(null)
  return (
    <div>
      <div className="flex gap-1.5 flex-wrap">
        {Array.from({ length: 11 }, (_, i) => i).map(n => (
          <button key={n} type="button"
            onMouseEnter={() => setHovered(n)}
            onMouseLeave={() => setHovered(null)}
            onClick={() => onChange(n)}
            className={`w-10 h-10 rounded text-sm font-bold transition-all duration-150
              ${value === n ? 'bg-gold text-dark' :
                n <= (hovered ?? -1) ? 'bg-gold/30 text-gold' : 'bg-dark-100 text-white/50 hover:bg-gold/20 hover:text-gold'}`}>
            {n}
          </button>
        ))}
      </div>
      <div className="flex justify-between mt-2 text-[10px] text-white/25 tracking-widest uppercase">
        <span>Not at all likely</span>
        <span>Extremely likely</span>
      </div>
    </div>
  )
}

function YesNo({ question, value, onChange }) {
  return (
    <div className="flex gap-4">
      {['Yes', 'No'].map(opt => (
        <button key={opt} type="button" onClick={() => onChange(opt)}
          className={`px-8 py-3 border text-sm tracking-widest uppercase transition-all duration-200
            ${value === opt ? 'border-gold bg-gold/10 text-gold' : 'border-white/15 text-white/50 hover:border-gold/40 hover:text-white'}`}>
          {opt}
        </button>
      ))}
    </div>
  )
}

function Dropdown({ question, value, onChange }) {
  const opts = question.options || []
  return (
    <select value={value || ''} onChange={e => onChange(e.target.value)}
      className="w-full bg-dark-100 border border-white/15 text-white px-4 py-3 text-sm focus:border-gold focus:outline-none transition-colors duration-200 rounded">
      <option value="">Select an option…</option>
      {opts.map((opt, i) => <option key={i} value={opt}>{opt}</option>)}
    </select>
  )
}

function TextInput({ question, value, onChange }) {
  return (
    <input type="text" value={value || ''} onChange={e => onChange(e.target.value)}
      placeholder={question.settings?.placeholder || 'Your answer…'}
      className="w-full bg-dark-100 border border-white/15 text-white px-4 py-3 text-sm placeholder-white/25 focus:border-gold focus:outline-none transition-colors duration-200 rounded" />
  )
}

function TextAreaInput({ question, value, onChange }) {
  return (
    <textarea value={value || ''} onChange={e => onChange(e.target.value)} rows={4}
      placeholder={question.settings?.placeholder || 'Your answer…'}
      className="w-full bg-dark-100 border border-white/15 text-white px-4 py-3 text-sm placeholder-white/25 focus:border-gold focus:outline-none transition-colors duration-200 rounded resize-none" />
  )
}

// ── Question renderer ─────────────────────────────────────────────────────────
function QuestionInput({ question, value, onChange }) {
  const props = { question, value, onChange }
  switch (question.type) {
    case 'single_choice':   return <SingleChoice {...props} />
    case 'multiple_choice': return <MultipleChoice {...props} />
    case 'rating':          return <Rating {...props} />
    case 'nps':             return <NPS {...props} />
    case 'yes_no':          return <YesNo {...props} />
    case 'dropdown':        return <Dropdown {...props} />
    case 'text_short':      return <TextInput {...props} />
    case 'text_long':       return <TextAreaInput {...props} />
    default:                return null
  }
}

// ── Main Survey Page ──────────────────────────────────────────────────────────
export default function SurveyPage() {
  const { slug }    = useParams()
  const [survey,    setSurvey]    = useState(null)
  const [loading,   setLoading]   = useState(true)
  const [error,     setError]     = useState(null)
  const [step,      setStep]      = useState(0)      // current question index
  const [answers,   setAnswers]   = useState({})     // { questionId: value }
  const [submitted, setSubmitted] = useState(false)
  const [submitting,setSubmitting]= useState(false)
  const [submitError, setSubmitError] = useState(null)

  // Session token for deduplication
  const tokenKey = `survey_token_${slug}`
  const [token] = useState(() => {
    const existing = sessionStorage.getItem(tokenKey)
    if (existing) return existing
    const t = Math.random().toString(36).substr(2) + Date.now().toString(36)
    sessionStorage.setItem(tokenKey, t)
    return t
  })

  useEffect(() => {
    fetchSurvey(slug)
      .then(res => { setSurvey(res.data.data); setLoading(false) })
      .catch(err => { setError(err.response?.data?.error || 'Survey not found.'); setLoading(false) })
  }, [slug])

  if (loading) return (
    <div className="min-h-screen bg-dark flex items-center justify-center">
      <div className="text-center">
        <div className="w-8 h-8 border-2 border-gold border-t-transparent rounded-full animate-spin mx-auto mb-4" />
        <p className="text-white/40 text-sm">Loading survey…</p>
      </div>
    </div>
  )

  if (error) return (
    <div className="min-h-screen bg-dark flex items-center justify-center px-6">
      <div className="text-center max-w-md">
        <p className="text-5xl mb-6">📋</p>
        <p className="font-serif text-2xl text-white/60 font-light mb-3">{error}</p>
        <Link to="/" className="text-gold text-xs tracking-widest uppercase hover:underline">← Back to Home</Link>
      </div>
    </div>
  )

  const questions = survey.questions || []
  const total     = questions.length
  const current   = questions[step]
  const progress  = total > 0 ? Math.round(((step) / total) * 100) : 0

  const setAnswer = (qId, val) => setAnswers(a => ({ ...a, [qId]: val }))

  const canProceed = () => {
    if (!current) return true
    if (!current.is_required) return true
    const val = answers[current.id]
    if (val === undefined || val === null || val === '') return false
    if (Array.isArray(val) && val.length === 0) return false
    return true
  }

  const handleSubmit = async () => {
    setSubmitting(true)
    setSubmitError(null)
    try {
      await submitSurvey(slug, {
        answers: Object.fromEntries(
          Object.entries(answers).map(([k, v]) => [k, Array.isArray(v) ? v : [v]])
        ),
      }, token)
      sessionStorage.removeItem(tokenKey)
      setSubmitted(true)
    } catch (e) {
      setSubmitError(e.response?.data?.error || 'Something went wrong. Please try again.')
    } finally {
      setSubmitting(false)
    }
  }

  // Thank you screen
  if (submitted) return (
    <div className="min-h-screen bg-dark flex items-center justify-center px-6">
      <motion.div initial={{ opacity: 0, scale: 0.9 }} animate={{ opacity: 1, scale: 1 }}
        className="text-center max-w-lg">
        <div className="text-6xl mb-6">🙏</div>
        <h1 className="font-serif text-4xl text-white font-light mb-4">Thank You!</h1>
        <p className="text-white/50 leading-relaxed mb-8">{survey.thank_you_message}</p>
        {survey.redirect_url ? (
          <a href={survey.redirect_url} className="inline-block px-10 py-4 bg-gold text-dark text-[10px] tracking-[0.35em] uppercase font-bold hover:bg-amber-400 transition-colors duration-300">
            Continue →
          </a>
        ) : (
          <Link to="/collections" className="inline-block px-10 py-4 bg-gold text-dark text-[10px] tracking-[0.35em] uppercase font-bold hover:bg-amber-400 transition-colors duration-300">
            Explore Collection →
          </Link>
        )}
      </motion.div>
    </div>
  )

  return (
    <div className="min-h-screen bg-dark pb-24">
      <SEO title={`${survey.title} | Artisan Leather`} description={survey.description || ''} url={`/survey/${slug}`} noIndex />

      {/* Header */}
      <div className="relative pt-32 pb-10 px-6 lg:px-12 border-b border-gold/10 bg-gradient-to-b from-dark-100 to-dark">
        <div className="max-w-2xl mx-auto">
          <nav className="flex items-center gap-2 text-[10px] tracking-[0.3em] uppercase text-white/25 mb-6">
            <Link to="/" className="hover:text-gold transition-colors">Home</Link>
            <span>›</span>
            <span className="text-gold/60">Survey</span>
          </nav>
          <p className="text-gold/60 tracking-[0.4em] uppercase text-[10px] mb-3">Artisan Leather</p>
          <h1 className="font-serif text-3xl md:text-4xl text-white font-light mb-3">{survey.title}</h1>
          {survey.description && <p className="text-white/40 text-sm leading-relaxed">{survey.description}</p>}
        </div>
      </div>

      {/* Progress bar */}
      {survey.show_progress && total > 0 && (
        <div className="sticky top-16 z-30 bg-dark/95 backdrop-blur-sm border-b border-white/5">
          <div className="max-w-2xl mx-auto px-6 py-3 flex items-center gap-4">
            <span className="text-[10px] text-white/30 tracking-widest uppercase flex-shrink-0">
              {step + 1} of {total}
            </span>
            <div className="flex-1 h-1 bg-white/10 rounded-full overflow-hidden">
              <motion.div className="h-full bg-gold rounded-full"
                initial={{ width: 0 }}
                animate={{ width: `${progress}%` }}
                transition={{ duration: 0.4 }} />
            </div>
            <span className="text-[10px] text-gold/60 flex-shrink-0">{progress}%</span>
          </div>
        </div>
      )}

      {/* Question card */}
      <div className="max-w-2xl mx-auto px-6 lg:px-12 pt-12">
        <AnimatePresence mode="wait">
          {current && (
            <motion.div key={step}
              initial={{ opacity: 0, x: 30 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: -30 }}
              transition={{ duration: 0.3 }}>

              <div className="mb-8">
                <div className="flex items-start gap-4 mb-2">
                  <span className="flex-shrink-0 w-8 h-8 rounded-full bg-gold/10 border border-gold/30 flex items-center justify-center text-gold text-xs font-bold">
                    {step + 1}
                  </span>
                  <div>
                    <h2 className="font-serif text-xl text-white font-light leading-snug">
                      {current.question}
                      {current.is_required && <span className="text-gold ml-1">*</span>}
                    </h2>
                    {current.description && (
                      <p className="text-white/40 text-sm mt-2 leading-relaxed">{current.description}</p>
                    )}
                  </div>
                </div>
              </div>

              <div className="ml-12">
                <QuestionInput
                  question={current}
                  value={answers[current.id]}
                  onChange={val => setAnswer(current.id, val)}
                />
              </div>

              {/* Required notice */}
              {current.is_required && !canProceed() && answers[current.id] !== undefined && (
                <p className="ml-12 mt-3 text-red-400 text-xs">Please answer this question to continue.</p>
              )}

            </motion.div>
          )}
        </AnimatePresence>

        {/* Navigation */}
        <div className="flex items-center justify-between mt-12 pt-8 border-t border-white/8">
          <button
            onClick={() => setStep(s => Math.max(0, s - 1))}
            disabled={step === 0}
            className="text-white/30 hover:text-white text-sm tracking-widest uppercase transition-colors duration-200 disabled:opacity-0 disabled:cursor-default">
            ← Previous
          </button>

          <div className="flex gap-2">
            {questions.map((_, i) => (
              <div key={i} className={`w-2 h-2 rounded-full transition-all duration-300
                ${i === step ? 'bg-gold scale-125' : i < step ? 'bg-gold/40' : 'bg-white/15'}`} />
            ))}
          </div>

          {step < total - 1 ? (
            <button
              onClick={() => { if (canProceed()) setStep(s => s + 1) }}
              disabled={!canProceed()}
              className={`px-8 py-3 text-[10px] tracking-[0.3em] uppercase font-bold transition-all duration-200
                ${canProceed() ? 'bg-gold text-dark hover:bg-amber-400' : 'bg-white/10 text-white/30 cursor-not-allowed'}`}>
              Next →
            </button>
          ) : (
            <button
              onClick={handleSubmit}
              disabled={!canProceed() || submitting}
              className={`px-8 py-3 text-[10px] tracking-[0.3em] uppercase font-bold transition-all duration-200
                ${canProceed() && !submitting ? 'bg-gold text-dark hover:bg-amber-400' : 'bg-white/10 text-white/30 cursor-not-allowed'}`}>
              {submitting ? 'Submitting…' : 'Submit Survey ✓'}
            </button>
          )}
        </div>

        {submitError && (
          <p className="text-center text-red-400 text-sm mt-4">{submitError}</p>
        )}
      </div>
    </div>
  )
}
