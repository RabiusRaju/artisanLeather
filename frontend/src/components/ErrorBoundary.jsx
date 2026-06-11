import { Component } from 'react'
import { HiOutlineExclamationTriangle } from 'react-icons/hi2'

export default class ErrorBoundary extends Component {
  constructor(props) {
    super(props)
    this.state = { hasError: false }
  }

  static getDerivedStateFromError() {
    return { hasError: true }
  }

  componentDidCatch(error, info) {
    console.error('Unhandled error caught by ErrorBoundary:', error, info)
  }

  handleReload = () => {
    window.location.href = '/'
  }

  render() {
    if (this.state.hasError) {
      return (
        <div className="min-h-screen bg-dark flex items-center justify-center px-6">
          <div className="text-center max-w-md">
            <HiOutlineExclamationTriangle size={48} className="text-gold/60 mx-auto mb-6" />
            <h1 className="font-serif text-3xl text-white font-light mb-4">Something went wrong</h1>
            <p className="text-white/40 font-light mb-10 text-sm">
              We're sorry for the inconvenience. Please return to the homepage and try again.
            </p>
            <button
              onClick={this.handleReload}
              className="inline-block px-10 py-4 bg-gold text-dark text-[10px] tracking-[0.35em] uppercase font-bold hover:bg-gold-300 transition-all duration-300"
            >
              Back to Home
            </button>
          </div>
        </div>
      )
    }

    return this.props.children
  }
}
