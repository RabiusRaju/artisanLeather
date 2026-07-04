import { useState, useEffect } from 'react'
import { useParams, Link } from 'react-router-dom'
import { motion } from 'framer-motion'
import { useTranslation } from 'react-i18next'
import SEO from '../components/SEO'
import { fetchSurvey, submitSurvey, verifyStaffPin } from '../services/api'

const STAFF_PIN_KEY = 'al_survey_staff_pin'

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

function Rating({ value, onChange }) {
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

function NPS({ value, onChange }) {
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

function YesNo({ value, onChange }) {
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

function ImageChoice({ question, value, onChange }) {
  const opts = question.options || []
  return (
    <div className="grid grid-cols-2 sm:grid-cols-3 gap-4">
      {opts.map((opt, i) => {
        const selected = value === opt.label
        return (
          <button key={i} type="button" onClick={() => onChange(opt.label)}
            className={`group relative rounded-lg overflow-hidden border-2 transition-all duration-200 text-left
              ${selected ? 'border-gold' : 'border-white/10 hover:border-gold/40'}`}>
            {opt.image ? (
              <img src={opt.image} alt={opt.label} className="w-full h-32 object-cover" />
            ) : (
              <div className="w-full h-32 bg-dark-100 flex items-center justify-center text-white/20 text-xs">No image</div>
            )}
            <div className={`px-3 py-2 text-xs text-center transition-colors duration-200
              ${selected ? 'bg-gold text-dark font-bold' : 'bg-dark-100 text-white/70'}`}>
              {opt.label}
            </div>
            {selected && (
              <div className="absolute top-2 right-2 w-5 h-5 rounded-full bg-gold flex items-center justify-center text-dark text-xs font-bold">✓</div>
            )}
          </button>
        )
      })}
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
    case 'image_choice':    return <ImageChoice {...props} />
    case 'text_short':      return <TextInput {...props} />
    case 'text_long':       return <TextAreaInput {...props} />
    default:                return null
  }
}

// ── Staff Mode control — lets field staff unlock repeat submissions on this device ──
function StaffModeControl({ unlocked, onUnlock }) {
  const [open, setOpen] = useState(false)
  const [pin, setPin] = useState('')
  const [error, setError] = useState(null)
  const [busy, setBusy] = useState(false)

  if (unlocked) {
    return (
      <p className="text-center text-[10px] text-gold/50 tracking-widest uppercase mt-3">
        🔓 Staff Mode — repeat submissions enabled on this device
      </p>
    )
  }

  if (!open) {
    return (
      <button onClick={() => setOpen(true)}
        className="block mx-auto mt-3 text-[10px] text-white/20 hover:text-white/40 tracking-widest uppercase transition-colors duration-200">
        Collecting feedback in person? Staff Mode
      </button>
    )
  }

  const submit = async () => {
    if (!pin.trim()) return
    setBusy(true)
    setError(null)
    try {
      const res = await verifyStaffPin(pin.trim())
      if (res.data?.success) {
        localStorage.setItem(STAFF_PIN_KEY, pin.trim())
        onUnlock()
        setOpen(false)
      } else {
        setError('Incorrect PIN.')
      }
    } catch {
      setError('Incorrect PIN.')
    } finally {
      setBusy(false)
    }
  }

  return (
    <div className="max-w-xs mx-auto mt-4 flex flex-col items-center gap-2">
      <input type="password" value={pin} onChange={e => setPin(e.target.value)}
        onKeyDown={e => e.key === 'Enter' && submit()}
        placeholder="Enter staff PIN"
        className="w-full bg-dark-100 border border-white/15 text-white px-3 py-2 text-sm text-center placeholder-white/25 focus:border-gold focus:outline-none transition-colors duration-200 rounded" />
      <div className="flex gap-2">
        <button onClick={submit} disabled={busy}
          className="px-5 py-1.5 bg-gold text-dark text-[10px] tracking-widest uppercase font-bold hover:bg-amber-400 transition-colors duration-200 disabled:opacity-50">
          {busy ? '…' : 'Unlock'}
        </button>
        <button onClick={() => { setOpen(false); setError(null) }}
          className="px-5 py-1.5 border border-white/15 text-white/40 text-[10px] tracking-widest uppercase hover:text-white/70 transition-colors duration-200">
          Cancel
        </button>
      </div>
      {error && <p className="text-red-400 text-xs">{error}</p>}
    </div>
  )
}

// ── Main Survey Page ──────────────────────────────────────────────────────────
export default function SurveyPage() {
  const { slug }    = useParams()
  const { i18n }    = useTranslation()
  const isAr        = i18n.language?.startsWith('ar')
  const [survey,    setSurvey]    = useState(null)
  const [loading,   setLoading]   = useState(true)
  const [error,     setError]     = useState(null)
  const [answers,   setAnswers]   = useState({})
  const [errors,    setErrors]    = useState({})
  const [submitted, setSubmitted] = useState(false)
  const [submitting,setSubmitting]= useState(false)
  const [submitError, setSubmitError] = useState(null)

  const tokenKey = `survey_token_${slug}`
  const generateToken = () => {
    const t = Math.random().toString(36).substr(2) + Date.now().toString(36)
    sessionStorage.setItem(tokenKey, t)
    return t
  }
  const [token, setToken] = useState(() => sessionStorage.getItem(tokenKey) || generateToken())

  const [staffUnlocked, setStaffUnlocked] = useState(() => !!localStorage.getItem(STAFF_PIN_KEY))

  const loadSurvey = () => {
    setLoading(true)
    setError(null)
    fetchSurvey(slug)
      .then(res => { setSurvey(res.data.data); setLoading(false) })
      .catch(err => { setError(err.response?.data?.error || 'Survey not found.'); setLoading(false) })
  }

  useEffect(() => { loadSurvey() }, [slug])

  const startNewResponse = () => {
    setToken(generateToken())
    setAnswers({})
    setErrors({})
    setSubmitted(false)
    setSubmitError(null)
  }

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
        <StaffModeControl unlocked={staffUnlocked} onUnlock={() => { setStaffUnlocked(true); loadSurvey() }} />
      </div>
    </div>
  )

  const questions   = survey.questions || []
  const surveyTitle = (isAr && survey.title_ar) ? survey.title_ar : survey.title
  const surveyDesc  = (isAr && survey.description_ar) ? survey.description_ar : survey.description
  const thankYouMsg = (isAr && survey.thank_you_message_ar) ? survey.thank_you_message_ar : survey.thank_you_message

  const transformQuestion = (q) => ({
    ...q,
    question: (isAr && q.question_ar) ? q.question_ar : q.question,
    options: q.type === 'image_choice'
      ? (q.options || []).map(o => ({ ...o, label: (isAr && o.label_ar) ? o.label_ar : o.label }))
      : ((isAr && q.options_ar?.length) ? q.options_ar : q.options),
  })

  const setAnswer = (qId, val) => {
    setAnswers(a => ({ ...a, [qId]: val }))
    if (errors[qId]) setErrors(e => ({ ...e, [qId]: null }))
  }

  const handleSubmit = async () => {
    // Validate required questions
    const newErrors = {}
    questions.forEach(q => {
      if (!q.is_required) return
      const val = answers[q.id]
      if (val === undefined || val === null || val === '' || (Array.isArray(val) && val.length === 0)) {
        newErrors[q.id] = 'This question is required.'
      }
    })
    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors)
      const firstId = Object.keys(newErrors)[0]
      document.getElementById(`question-${firstId}`)?.scrollIntoView({ behavior: 'smooth', block: 'center' })
      return
    }

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
        <p className="text-white/50 leading-relaxed mb-8">{thankYouMsg}</p>
        {survey.redirect_url ? (
          <a href={survey.redirect_url} className="inline-block px-10 py-4 bg-gold text-dark text-[10px] tracking-[0.35em] uppercase font-bold hover:bg-amber-400 transition-colors duration-300">
            Continue →
          </a>
        ) : (
          <Link to="/collections" className="inline-block px-10 py-4 bg-gold text-dark text-[10px] tracking-[0.35em] uppercase font-bold hover:bg-amber-400 transition-colors duration-300">
            Explore Collection →
          </Link>
        )}
        {staffUnlocked && (
          <button onClick={startNewResponse}
            className="block mx-auto mt-6 px-10 py-3 border border-gold/40 text-gold text-[10px] tracking-[0.35em] uppercase font-bold hover:bg-gold/10 transition-colors duration-300">
            📝 Submit Another Response (Next Customer)
          </button>
        )}
      </motion.div>
    </div>
  )

  return (
    <div className="min-h-screen bg-dark pb-24">
      <SEO title={`${surveyTitle} | Artisan Leather`} description={surveyDesc || ''} url={`/survey/${slug}`} noIndex />

      {/* Header */}
      <div className="relative pt-32 pb-10 px-6 lg:px-12 border-b border-gold/10 bg-gradient-to-b from-dark-100 to-dark">
        <div className="max-w-2xl mx-auto">
          <nav className="flex items-center gap-2 text-[10px] tracking-[0.3em] uppercase text-white/25 mb-6">
            <Link to="/" className="hover:text-gold transition-colors">Home</Link>
            <span>›</span>
            <span className="text-gold/60">Survey</span>
          </nav>
          <p className="text-gold/60 tracking-[0.4em] uppercase text-[10px] mb-3">Artisan Leather</p>
          <h1 className="font-serif text-3xl md:text-4xl text-white font-light mb-3">{surveyTitle}</h1>
          {surveyDesc && <p className="text-white/40 text-sm leading-relaxed">{surveyDesc}</p>}
        </div>
      </div>

      {/* All questions */}
      <div className="max-w-2xl mx-auto px-6 lg:px-12 pt-10 space-y-10">
        {questions.map((q, index) => {
          const tq = transformQuestion(q)
          return (
            <motion.div
              key={q.id}
              id={`question-${q.id}`}
              initial={{ opacity: 0, y: 16 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.3, delay: index * 0.05 }}
              className={`p-6 rounded-lg border transition-colors duration-200 ${
                errors[q.id] ? 'border-red-500/40 bg-red-500/5' : 'border-white/8 bg-dark-100/40'
              }`}>

              {/* Question header */}
              <div className="flex items-start gap-4 mb-5">
                <span className="flex-shrink-0 w-8 h-8 rounded-full bg-gold/10 border border-gold/30 flex items-center justify-center text-gold text-xs font-bold">
                  {index + 1}
                </span>
                <div className="flex-1">
                  <h2 className="font-serif text-lg text-white font-light leading-snug">
                    {tq.question}
                    {tq.is_required && <span className="text-gold ml-1">*</span>}
                  </h2>
                  {tq.description && (
                    <p className="text-white/40 text-sm mt-1.5 leading-relaxed">{tq.description}</p>
                  )}
                </div>
              </div>

              {tq.image && (
                <img src={tq.image} alt={tq.question}
                  className="ml-12 mb-4 max-h-60 w-auto rounded-lg border border-white/10 object-cover" />
              )}

              <div className="ml-12">
                <QuestionInput
                  question={tq}
                  value={answers[q.id]}
                  onChange={val => setAnswer(q.id, val)}
                />
              </div>

              {errors[q.id] && (
                <p className="ml-12 mt-3 text-red-400 text-xs">{errors[q.id]}</p>
              )}
            </motion.div>
          )
        })}

        {/* Submit */}
        <div className="pt-4 pb-8 border-t border-white/8">
          {submitError && (
            <p className="text-center text-red-400 text-sm mb-4">{submitError}</p>
          )}
          <button
            onClick={handleSubmit}
            disabled={submitting}
            className={`w-full py-4 text-[11px] tracking-[0.35em] uppercase font-bold transition-all duration-200
              ${submitting ? 'bg-white/10 text-white/30 cursor-not-allowed' : 'bg-gold text-dark hover:bg-amber-400'}`}>
            {submitting ? 'Submitting…' : 'Submit Survey ✓'}
          </button>
          <p className="text-center text-white/20 text-xs mt-3">
            Fields marked <span className="text-gold">*</span> are required
          </p>
        </div>

        <StaffModeControl unlocked={staffUnlocked} onUnlock={() => setStaffUnlocked(true)} />
      </div>
    </div>
  )
}
