// supabase-progress.js
// Motor de progresso — usado por inss.html, tjce.html e rfb.html

const SUPABASE_URL  = 'https://cgfzhltysavnwmqhaskj.supabase.co'
const SUPABASE_ANON = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImNnZnpobHR5c2F2bndtcWhhc2tqIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzU4MjA4NDQsImV4cCI6MjA5MTM5Njg0NH0.SJDMkHsilRXNsg6ar9rM1AYPDXzxauUOgxunHmTM9Jc'

const _sb = supabase.createClient(SUPABASE_URL, SUPABASE_ANON)

// Cache local para evitar múltiplas chamadas ao banco
let _progressCache = null
let _sessionCache  = null

// ─── Sessão ───────────────────────────────────────────
async function getSession() {
  if (_sessionCache) return _sessionCache

  // Aguarda token OAuth na URL ser processado
  const session = await new Promise(resolve => {
    const { data: listener } = _sb.auth.onAuthStateChange((event, sess) => {
      listener.subscription.unsubscribe()
      resolve(sess)
    })
  })

  // Fallback: tenta getSession direto
  if (!session) {
    const { data } = await _sb.auth.getSession()
    _sessionCache = data.session
    return _sessionCache
  }

  _sessionCache = session
  return session
}

// ─── Carrega TODO o progresso do usuário ──────────────
async function loadAllProgress() {
  if (_progressCache) return _progressCache

  const session = await getSession()
  if (!session) return {}

  const { data, error } = await _sb
    .from('user_progress')
    .select('page_id, completed, score, total, answers')
    .eq('user_id', session.user.id)

  if (error) {
    console.warn('Erro ao carregar progresso:', error)
    return {}
  }

  // Transforma array em objeto { page_id: { completed, score, total, answers } }
  _progressCache = {}
  data.forEach(row => {
    _progressCache[row.page_id] = {
      completed: row.completed,
      score:     row.score,
      total:     row.total,
      answers:   row.answers
    }
  })

  return _progressCache
}

// ─── Verifica se página está concluída ────────────────
async function isDone(pageId) {
  const all = await loadAllProgress()
  return !!(all[pageId]?.completed)
}

// ─── Marca página como concluída e salva no Supabase ──
async function markDone(pageId, score, total, answers) {
  const session = await getSession()
  if (!session) return

  const row = {
    user_id:   session.user.id,
    page_id:   pageId,
    completed: true,
    score:     score,
    total:     total,
    answers:   answers,
    updated_at: new Date().toISOString()
  }

  const { error } = await _sb
    .from('user_progress')
    .upsert(row, { onConflict: 'user_id,page_id' })

  if (error) {
    console.warn('Erro ao salvar progresso:', error)
    return
  }

  // Atualiza o cache local
  if (_progressCache) {
    _progressCache[pageId] = { completed: true, score, total, answers }
  }

  console.log(`✅ Progresso salvo: ${pageId} — ${score}/${total}`)
}

// ─── Compatibilidade com código legado (localStorage) ──
function loadProgress() {
  try {
    return JSON.parse(localStorage.getItem('talento_progresso_v1')) || {}
  } catch {
    return {}
  }
}