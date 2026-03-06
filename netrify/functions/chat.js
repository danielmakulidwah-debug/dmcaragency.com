// ═══════════════════════════════════════
// DM Car Agency — Netlify Serverless Function
// Securely calls Groq API using env variable
// GROQ_API_KEY is set in Netlify dashboard
// ═══════════════════════════════════════

const https = require('https');

function httpsPost(url, headers, body) {
  return new Promise((resolve, reject) => {
    const urlObj = new URL(url);
    const options = {
      hostname: urlObj.hostname,
      path: urlObj.pathname,
      method: 'POST',
      headers: { ...headers, 'Content-Length': Buffer.byteLength(body) }
    };
    const req = https.request(options, res => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => resolve({ status: res.statusCode, body: data }));
    });
    req.on('error', reject);
    req.write(body);
    req.end();
  });
}

exports.handler = async function(event) {

  // CORS headers so browser can call this function
  const corsHeaders = {
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Headers': 'Content-Type',
    'Access-Control-Allow-Methods': 'POST, OPTIONS'
  };

  // Handle preflight
  if (event.httpMethod === 'OPTIONS') {
    return { statusCode: 200, headers: corsHeaders, body: '' };
  }

  if (event.httpMethod !== 'POST') {
    return { statusCode: 405, headers: corsHeaders, body: JSON.stringify({ error: 'Method not allowed' }) };
  }

  const apiKey = process.env.GROQ_API_KEY;
  if (!apiKey) {
    return {
      statusCode: 500,
      headers: corsHeaders,
      body: JSON.stringify({ error: 'API key not configured on server.' })
    };
  }

  let parsed;
  try {
    parsed = JSON.parse(event.body);
  } catch {
    return { statusCode: 400, headers: corsHeaders, body: JSON.stringify({ error: 'Invalid request body' }) };
  }

  try {
    const payload = JSON.stringify({
      model: 'llama-3.3-70b-versatile',
      messages: parsed.messages,
      temperature: 0.7,
      max_tokens: 500,
      top_p: 0.9
    });

    const result = await httpsPost(
      'https://api.groq.com/openai/v1/chat/completions',
      {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${apiKey}`
      },
      payload
    );

    const data = JSON.parse(result.body);

    if (result.status !== 200) {
      return {
        statusCode: result.status,
        headers: corsHeaders,
        body: JSON.stringify({ error: data.error?.message || `Groq error ${result.status}` })
      };
    }

    const reply = data.choices?.[0]?.message?.content || '';
    return {
      statusCode: 200,
      headers: { ...corsHeaders, 'Content-Type': 'application/json' },
      body: JSON.stringify({ reply })
    };

  } catch (err) {
    return {
      statusCode: 500,
      headers: corsHeaders,
      body: JSON.stringify({ error: err.message || 'Internal server error' })
    };
  }
};
