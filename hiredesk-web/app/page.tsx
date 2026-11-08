async function getPing() {
  const res = await fetch('http://127.0.0.1:8000/api/ping', {
    cache: 'no-store',
  });
  if (!res.ok) throw new Error('Failed to fetch');
  return res.json();
}

export default async function Home() {
  const data = await getPing();

  return (
    <main className="min-h-screen p-10 bg-gray-50">
      <div className="max-w-2xl mx-auto">
        <h1 className="text-3xl font-bold mb-6">HireDesk — Connection Test</h1>
        <div className="p-6 bg-white rounded-lg shadow">
          <p className="text-green-600 font-semibold mb-2">✅ Connected</p>
          <p className="text-gray-700">
            <strong>{data.app} says:</strong> {data.message}
          </p>
          <p className="text-gray-500 text-sm mt-2">Server time: {data.time}</p>
        </div>
      </div>
    </main>
  );
}