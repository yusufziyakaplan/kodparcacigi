import React, { useState, useEffect } from 'react';
import { Code2, Plus, Search, User, Eye, Copy, Edit, Trash2, Star, Globe, Clock, Tag, Download, Share, X, Save, Heart } from 'lucide-react';

interface Snippet {
  id: number;
  title: string;
  description: string;
  code: string;
  language: string;
  category: string;
  tags: string[];
  created_at: string;
  updated_at: string;
  views: number;
  is_favorite: boolean;
  user_id: number;
}

interface User {
  id: number;
  username: string;
  email: string;
  first_name: string;
  last_name: string;
}

const languageColors: { [key: string]: string } = {
  javascript: '#F7DF1E',
  typescript: '#3178C6',
  python: '#3776AB',
  html: '#E34F26',
  css: '#1572B6',
  sql: '#4479A1',
  php: '#777BB4',
  java: '#ED8B00',
  csharp: '#239120',
  cpp: '#00599C',
  go: '#00ADD8',
  rust: '#000000',
  ruby: '#CC342D'
};

const categoryIcons: { [key: string]: string } = {
  'Web Development': 'fas fa-globe',
  'Algorithms': 'fas fa-code-branch',
  'Database': 'fas fa-database',
  'Utilities': 'fas fa-tools',
  'API': 'fas fa-plug',
  'Authentication': 'fas fa-lock',
  'UI Components': 'fas fa-palette',
  'Testing': 'fas fa-vial'
};

function App() {
  const [user, setUser] = useState<User | null>(null);
  const [snippets, setSnippets] = useState<Snippet[]>([]);
  const [filteredSnippets, setFilteredSnippets] = useState<Snippet[]>([]);
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedLanguage, setSelectedLanguage] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('');
  const [sortBy, setSortBy] = useState('created_at');
  const [showAddModal, setShowAddModal] = useState(false);
  const [showDetailModal, setShowDetailModal] = useState(false);
  const [editingSnippet, setEditingSnippet] = useState<Snippet | null>(null);
  const [selectedSnippet, setSelectedSnippet] = useState<Snippet | null>(null);

  // Mock data
  const mockUser: User = {
    id: 1,
    username: 'demo_user',
    email: 'demo@example.com',
    first_name: 'Demo',
    last_name: 'User'
  };

  const mockSnippets: Snippet[] = [
    {
      id: 1,
      title: "React useState Hook Kullanımı",
      description: "React'ta state yönetimi için useState hook'unun temel kullanımı",
      code: `import { useState } from 'react';

function Example() {
  const [count, setCount] = useState(0);

  return (
    <div>
      <p>You clicked {count} times</p>
      <button onClick={() => setCount(count + 1)}>
        Click me
      </button>
    </div>
  );
}`,
      language: "javascript",
      category: "Web Development",
      tags: ["React", "Hooks", "State"],
      created_at: "2024-01-15",
      updated_at: "2024-01-15",
      views: 124,
      is_favorite: true,
      user_id: 1
    },
    {
      id: 2,
      title: "Python ile JSON Okuma/Yazma",
      description: "Python'da JSON dosyalarını okuma ve yazma işlemleri",
      code: `import json

# JSON okuma
with open('data.json', 'r') as file:
    data = json.load(file)

# JSON yazma
data['new_key'] = 'new_value'
with open('data.json', 'w') as file:
    json.dump(data, file, indent=4)`,
      language: "python",
      category: "Utilities",
      tags: ["Python", "JSON", "File"],
      created_at: "2024-01-14",
      updated_at: "2024-01-14",
      views: 87,
      is_favorite: false,
      user_id: 1
    },
    {
      id: 3,
      title: "CSS Grid Layout Örneği",
      description: "CSS Grid ile responsive layout oluşturma",
      code: `.container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 20px;
}

.item {
  background: #f0f0f0;
  padding: 20px;
  border-radius: 8px;
}

@media (max-width: 600px) {
  .container {
    grid-template-columns: 1fr;
  }
}`,
      language: "css",
      category: "UI Components",
      tags: ["CSS", "Grid", "Responsive"],
      created_at: "2024-01-13",
      updated_at: "2024-01-13",
      views: 98,
      is_favorite: false,
      user_id: 1
    },
    {
      id: 4,
      title: "SQL JOIN Örnekleri",
      description: "Farklı SQL JOIN türlerinin kullanım örnekleri",
      code: `-- INNER JOIN
SELECT orders.order_id, customers.name
FROM orders
INNER JOIN customers ON orders.customer_id = customers.id;

-- LEFT JOIN
SELECT customers.name, orders.order_id
FROM customers
LEFT JOIN orders ON customers.id = orders.customer_id;`,
      language: "sql",
      category: "Database",
      tags: ["SQL", "Database", "JOIN"],
      created_at: "2024-01-12",
      updated_at: "2024-01-12",
      views: 76,
      is_favorite: true,
      user_id: 1
    }
  ];

  useEffect(() => {
    setUser(mockUser);
    setSnippets(mockSnippets);
    setFilteredSnippets(mockSnippets);
  }, []);

  useEffect(() => {
    let filtered = snippets;

    if (searchTerm) {
      filtered = filtered.filter(snippet =>
        snippet.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
        snippet.description.toLowerCase().includes(searchTerm.toLowerCase()) ||
        snippet.code.toLowerCase().includes(searchTerm.toLowerCase()) ||
        snippet.tags.some(tag => tag.toLowerCase().includes(searchTerm.toLowerCase()))
      );
    }

    if (selectedLanguage) {
      filtered = filtered.filter(snippet => snippet.language === selectedLanguage);
    }

    if (selectedCategory) {
      filtered = filtered.filter(snippet => snippet.category === selectedCategory);
    }

    // Sort
    filtered.sort((a, b) => {
      switch (sortBy) {
        case 'created_at':
          return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
        case 'views':
          return b.views - a.views;
        case 'title':
          return a.title.localeCompare(b.title);
        default:
          return 0;
      }
    });

    setFilteredSnippets(filtered);
  }, [searchTerm, selectedLanguage, selectedCategory, sortBy, snippets]);

  const languages = [...new Set(snippets.map(s => s.language))];
  const categories = [...new Set(snippets.map(s => s.category))];
  const allTags = [...new Set(snippets.flatMap(s => s.tags))];

  const handleAddSnippet = (snippetData: Omit<Snippet, 'id' | 'created_at' | 'updated_at' | 'views' | 'user_id'>) => {
    const newSnippet: Snippet = {
      ...snippetData,
      id: Date.now(),
      created_at: new Date().toISOString().split('T')[0],
      updated_at: new Date().toISOString().split('T')[0],
      views: 0,
      user_id: user!.id
    };
    setSnippets([newSnippet, ...snippets]);
    setShowAddModal(false);
  };

  const handleEditSnippet = (snippetData: Omit<Snippet, 'id' | 'created_at' | 'updated_at' | 'views' | 'user_id'>) => {
    if (editingSnippet) {
      const updatedSnippets = snippets.map(s =>
        s.id === editingSnippet.id 
          ? { ...s, ...snippetData, updated_at: new Date().toISOString().split('T')[0] } 
          : s
      );
      setSnippets(updatedSnippets);
      setEditingSnippet(null);
    }
  };

  const handleDeleteSnippet = (id: number) => {
    if (confirm('Bu kod parçacığını silmek istediğinizden emin misiniz?')) {
      setSnippets(snippets.filter(s => s.id !== id));
    }
  };

  const handleViewSnippet = (snippet: Snippet) => {
    // Increment view count
    const updatedSnippets = snippets.map(s =>
      s.id === snippet.id ? { ...s, views: s.views + 1 } : s
    );
    setSnippets(updatedSnippets);
    setSelectedSnippet({ ...snippet, views: snippet.views + 1 });
    setShowDetailModal(true);
  };

  const toggleFavorite = (id: number) => {
    const updatedSnippets = snippets.map(s =>
      s.id === id ? { ...s, is_favorite: !s.is_favorite } : s
    );
    setSnippets(updatedSnippets);
  };

  const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
    // You could add a toast notification here
  };

  if (!user) {
    return <AuthPage onLogin={() => setUser(mockUser)} />;
  }

  return (
    <div className="min-h-screen bg-gray-100">
      {/* Navigation */}
      <nav className="bg-gradient-to-r from-blue-600 via-blue-700 to-purple-700 text-white shadow-lg">
        <div className="container mx-auto px-4 py-3 flex justify-between items-center">
          <div className="flex items-center space-x-3">
            <div className="bg-white bg-opacity-20 p-2 rounded-lg">
              <Code2 className="h-8 w-8" />
            </div>
            <h1 className="text-2xl font-bold">Snippet Manager</h1>
          </div>
          
          <div className="flex items-center space-x-4">
            {/* Search */}
            <div className="relative">
              <input
                type="text"
                placeholder="Ara..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="bg-blue-500 bg-opacity-50 text-white placeholder-blue-200 rounded-lg px-4 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-300 w-64"
              />
              <Search className="absolute right-3 top-2.5 h-5 w-5 text-blue-200" />
            </div>
            
            {/* New Snippet Button */}
            <button
              onClick={() => setShowAddModal(true)}
              className="bg-blue-700 hover:bg-blue-800 px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors"
            >
              <Plus className="h-5 w-5" />
              <span>Yeni Parçacık</span>
            </button>
            
            {/* User Menu */}
            <div className="flex items-center space-x-2">
              <div className="w-8 h-8 rounded-full bg-blue-400 flex items-center justify-center">
                <User className="h-5 w-5" />
              </div>
              <span className="text-sm">{user.first_name} {user.last_name}</span>
            </div>
          </div>
        </div>
      </nav>

      {/* Main Content */}
      <div className="container mx-auto px-4 py-6">
        <div className="flex flex-col lg:flex-row gap-6">
          {/* Sidebar */}
          <div className="w-full lg:w-80">
            <div className="bg-white rounded-xl shadow-lg p-6 mb-6">
              {/* User Stats */}
              <div className="text-center mb-6">
                <div className="w-20 h-20 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center mx-auto mb-3">
                  <User className="h-10 w-10 text-white" />
                </div>
                <h5 className="text-lg font-bold text-gray-800">{user.first_name} {user.last_name}</h5>
                <small className="text-gray-500">@{user.username}</small>
              </div>
              
              <div className="grid grid-cols-2 gap-3 mb-6">
                <div className="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-3 rounded-lg text-center">
                  <div className="text-xl font-bold">{snippets.length}</div>
                  <div className="text-xs opacity-90">Snippet</div>
                </div>
                <div className="bg-gradient-to-r from-green-500 to-green-600 text-white p-3 rounded-lg text-center">
                  <div className="text-xl font-bold">{snippets.reduce((sum, s) => sum + s.views, 0)}</div>
                  <div className="text-xs opacity-90">Görüntülenme</div>
                </div>
              </div>

              {/* Categories */}
              <h6 className="font-bold text-gray-700 mb-3 flex items-center">
                <i className="fas fa-folder mr-2"></i>Kategoriler
              </h6>
              <div className="space-y-1 mb-6">
                <button
                  onClick={() => setSelectedCategory('')}
                  className={`w-full text-left flex items-center p-2 rounded-lg transition-colors ${
                    selectedCategory === '' ? 'bg-blue-100 text-blue-700' : 'hover:bg-gray-100'
                  }`}
                >
                  <Star className="h-4 w-4 mr-2" />
                  Tümü
                  <span className="ml-auto bg-gray-200 text-gray-600 px-2 py-1 rounded-full text-xs">
                    {snippets.length}
                  </span>
                </button>
                {categories.map(category => (
                  <button
                    key={category}
                    onClick={() => setSelectedCategory(category)}
                    className={`w-full text-left flex items-center p-2 rounded-lg transition-colors ${
                      selectedCategory === category ? 'bg-blue-100 text-blue-700' : 'hover:bg-gray-100'
                    }`}
                  >
                    <i className={`${categoryIcons[category] || 'fas fa-folder'} mr-2 text-sm`}></i>
                    {category}
                    <span className="ml-auto bg-gray-200 text-gray-600 px-2 py-1 rounded-full text-xs">
                      {snippets.filter(s => s.category === category).length}
                    </span>
                  </button>
                ))}
              </div>

              {/* Languages */}
              <h6 className="font-bold text-gray-700 mb-3 flex items-center">
                <Code2 className="h-4 w-4 mr-2" />Diller
              </h6>
              <div className="space-y-1 mb-6">
                <button
                  onClick={() => setSelectedLanguage('')}
                  className={`w-full text-left flex items-center p-2 rounded-lg transition-colors ${
                    selectedLanguage === '' ? 'bg-blue-100 text-blue-700' : 'hover:bg-gray-100'
                  }`}
                >
                  <div className="w-3 h-3 rounded-full bg-gray-500 mr-2"></div>
                  Tümü
                </button>
                {languages.map(language => (
                  <button
                    key={language}
                    onClick={() => setSelectedLanguage(language)}
                    className={`w-full text-left flex items-center p-2 rounded-lg transition-colors ${
                      selectedLanguage === language ? 'bg-blue-100 text-blue-700' : 'hover:bg-gray-100'
                    }`}
                  >
                    <div 
                      className="w-3 h-3 rounded-full mr-2" 
                      style={{ backgroundColor: languageColors[language] || '#6B7280' }}
                    ></div>
                    {language}
                    <span className="ml-auto bg-gray-200 text-gray-600 px-2 py-1 rounded-full text-xs">
                      {snippets.filter(s => s.language === language).length}
                    </span>
                  </button>
                ))}
              </div>

              {/* Popular Tags */}
              <h6 className="font-bold text-gray-700 mb-3 flex items-center">
                <Tag className="h-4 w-4 mr-2" />Popüler Etiketler
              </h6>
              <div className="flex flex-wrap gap-2">
                {allTags.slice(0, 10).map(tag => (
                  <span
                    key={tag}
                    className="inline-block bg-blue-500 text-white px-2 py-1 rounded-full text-xs cursor-pointer hover:bg-blue-600 transition-colors"
                  >
                    {tag}
                  </span>
                ))}
              </div>
            </div>
          </div>

          {/* Main Content */}
          <div className="flex-1">
            {/* Header with Sort */}
            <div className="bg-white rounded-xl shadow-lg p-6 mb-6">
              <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                  <h4 className="text-2xl font-bold text-gray-800 flex items-center">
                    <Code2 className="h-6 w-6 mr-2" />
                    Kod Parçacıkları
                    <span className="ml-2 bg-blue-500 text-white px-3 py-1 rounded-full text-sm">
                      {filteredSnippets.length}
                    </span>
                  </h4>
                </div>
                <div className="flex items-center space-x-2">
                  <span className="text-sm text-gray-500">Sırala:</span>
                  <select
                    value={sortBy}
                    onChange={(e) => setSortBy(e.target.value)}
                    className="bg-white border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="created_at">En Yeni</option>
                    <option value="views">En Çok Görüntülenen</option>
                    <option value="title">Alfabetik</option>
                  </select>
                </div>
              </div>
            </div>

            {/* Snippets Grid */}
            {filteredSnippets.length === 0 ? (
              <div className="text-center py-12">
                <Code2 className="h-16 w-16 text-gray-400 mx-auto mb-4" />
                <h5 className="text-xl font-medium text-gray-600 mb-2">Henüz kod parçacığı bulunmuyor</h5>
                <p className="text-gray-500 mb-4">İlk kod parçacığınızı oluşturmak için "Yeni Parçacık" butonuna tıklayın.</p>
                <button
                  onClick={() => setShowAddModal(true)}
                  className="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2 mx-auto"
                >
                  <Plus className="h-5 w-5" />
                  <span>İlk Snippet'inizi Oluşturun</span>
                </button>
              </div>
            ) : (
              <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                {filteredSnippets.map(snippet => (
                  <SnippetCard
                    key={snippet.id}
                    snippet={snippet}
                    onView={handleViewSnippet}
                    onEdit={setEditingSnippet}
                    onDelete={handleDeleteSnippet}
                    onToggleFavorite={toggleFavorite}
                  />
                ))}
              </div>
            )}
          </div>
        </div>
      </div>

      {/* Modals */}
      {showAddModal && (
        <SnippetModal
          onSave={handleAddSnippet}
          onClose={() => setShowAddModal(false)}
          languages={languages}
          categories={categories}
        />
      )}

      {editingSnippet && (
        <SnippetModal
          snippet={editingSnippet}
          onSave={handleEditSnippet}
          onClose={() => setEditingSnippet(null)}
          languages={languages}
          categories={categories}
        />
      )}

      {showDetailModal && selectedSnippet && (
        <SnippetDetailModal
          snippet={selectedSnippet}
          onClose={() => setShowDetailModal(false)}
          onEdit={setEditingSnippet}
          onDelete={handleDeleteSnippet}
          onToggleFavorite={toggleFavorite}
          onCopy={copyToClipboard}
        />
      )}
    </div>
  );
}

function SnippetCard({ snippet, onView, onEdit, onDelete, onToggleFavorite }: {
  snippet: Snippet;
  onView: (snippet: Snippet) => void;
  onEdit: (snippet: Snippet) => void;
  onDelete: (id: number) => void;
  onToggleFavorite: (id: number) => void;
}) {
  return (
    <div className="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 cursor-pointer group">
      <div className="p-6">
        <div className="flex justify-between items-start mb-3">
          <h3 className="font-bold text-gray-800 text-lg group-hover:text-blue-600 transition-colors">
            {snippet.title}
          </h3>
          <div className="flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <button
              onClick={(e) => {
                e.stopPropagation();
                onToggleFavorite(snippet.id);
              }}
              className={`p-1 rounded transition-colors ${
                snippet.is_favorite ? 'text-red-500 hover:text-red-600' : 'text-gray-400 hover:text-red-500'
              }`}
            >
              <Heart className={`h-4 w-4 ${snippet.is_favorite ? 'fill-current' : ''}`} />
            </button>
            <button
              onClick={(e) => {
                e.stopPropagation();
                onEdit(snippet);
              }}
              className="p-1 text-gray-400 hover:text-blue-600 transition-colors"
            >
              <Edit className="h-4 w-4" />
            </button>
            <button
              onClick={(e) => {
                e.stopPropagation();
                onDelete(snippet.id);
              }}
              className="p-1 text-gray-400 hover:text-red-600 transition-colors"
            >
              <Trash2 className="h-4 w-4" />
            </button>
          </div>
        </div>
        
        <p className="text-gray-600 text-sm mb-3 line-clamp-2">
          {snippet.description}
        </p>
        
        {/* Code Preview */}
        <div className="bg-gray-900 rounded-lg p-3 mb-4 overflow-hidden">
          <div className="flex justify-between items-center mb-2">
            <div className="flex items-center">
              <div 
                className="w-3 h-3 rounded-full mr-2" 
                style={{ backgroundColor: languageColors[snippet.language] || '#6B7280' }}
              ></div>
              <span className="text-gray-300 text-xs">{snippet.language}</span>
            </div>
          </div>
          <pre className="text-gray-200 text-xs overflow-hidden" style={{ maxHeight: '100px' }}>
            <code>{snippet.code.substring(0, 150)}{snippet.code.length > 150 ? '...' : ''}</code>
          </pre>
        </div>
        
        <div className="flex justify-between items-center mb-3">
          <div className="flex items-center space-x-2">
            <span className="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
              {snippet.category}
            </span>
          </div>
          <div className="flex items-center text-gray-500 text-xs">
            <Eye className="h-3 w-3 mr-1" />
            <span>{snippet.views}</span>
          </div>
        </div>
        
        {/* Tags */}
        {snippet.tags.length > 0 && (
          <div className="flex flex-wrap gap-1 mb-4">
            {snippet.tags.slice(0, 3).map(tag => (
              <span key={tag} className="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs flex items-center">
                <Tag className="h-3 w-3 mr-1" />
                {tag}
              </span>
            ))}
            {snippet.tags.length > 3 && (
              <span className="text-gray-400 text-xs">+{snippet.tags.length - 3} more</span>
            )}
          </div>
        )}
        
        <div className="flex justify-between items-center">
          <small className="text-gray-500 flex items-center">
            <Clock className="h-3 w-3 mr-1" />
            {new Date(snippet.created_at).toLocaleDateString('tr-TR')}
          </small>
          <button
            onClick={() => onView(snippet)}
            className="bg-blue-600 text-white px-3 py-1 rounded-lg hover:bg-blue-700 transition-colors text-sm flex items-center space-x-1"
          >
            <Eye className="h-3 w-3" />
            <span>Görüntüle</span>
          </button>
        </div>
      </div>
    </div>
  );
}

function SnippetModal({ snippet, onSave, onClose, languages, categories }: {
  snippet?: Snippet;
  onSave: (data: Omit<Snippet, 'id' | 'created_at' | 'updated_at' | 'views' | 'user_id'>) => void;
  onClose: () => void;
  languages: string[];
  categories: string[];
}) {
  const [formData, setFormData] = useState({
    title: snippet?.title || '',
    description: snippet?.description || '',
    code: snippet?.code || '',
    language: snippet?.language || '',
    category: snippet?.category || '',
    tags: snippet?.tags.join(', ') || '',
    is_favorite: snippet?.is_favorite || false
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSave({
      ...formData,
      tags: formData.tags.split(',').map(tag => tag.trim()).filter(Boolean)
    });
  };

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div className="bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
        <div className="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6">
          <div className="flex justify-between items-center">
            <h3 className="text-xl font-bold flex items-center">
              <Plus className="h-6 w-6 mr-2" />
              {snippet ? 'Kod Parçacığını Düzenle' : 'Yeni Kod Parçacığı Ekle'}
            </h3>
            <button
              onClick={onClose}
              className="text-white hover:text-gray-200 transition-colors"
            >
              <X className="h-6 w-6" />
            </button>
          </div>
        </div>
        
        <div className="p-6 overflow-y-auto" style={{ maxHeight: 'calc(90vh - 140px)' }}>
          <form onSubmit={handleSubmit} className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div className="md:col-span-2">
                <label className="block text-gray-700 font-medium mb-2">Başlık</label>
                <input
                  type="text"
                  value={formData.title}
                  onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                  className="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Kod parçacığı başlığı"
                  required
                />
              </div>
              
              <div className="md:col-span-2">
                <label className="block text-gray-700 font-medium mb-2">Açıklama</label>
                <textarea
                  value={formData.description}
                  onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                  className="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  rows={3}
                  placeholder="Kod parçacığı açıklaması"
                />
              </div>
              
              <div>
                <label className="block text-gray-700 font-medium mb-2">Dil</label>
                <select
                  value={formData.language}
                  onChange={(e) => setFormData({ ...formData, language: e.target.value })}
                  className="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  required
                >
                  <option value="">Dil seçin...</option>
                  {Object.keys(languageColors).map(lang => (
                    <option key={lang} value={lang}>{lang}</option>
                  ))}
                </select>
              </div>
              
              <div>
                <label className="block text-gray-700 font-medium mb-2">Kategori</label>
                <select
                  value={formData.category}
                  onChange={(e) => setFormData({ ...formData, category: e.target.value })}
                  className="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  required
                >
                  <option value="">Kategori seçin...</option>
                  {Object.keys(categoryIcons).map(cat => (
                    <option key={cat} value={cat}>{cat}</option>
                  ))}
                </select>
              </div>
              
              <div className="md:col-span-2">
                <label className="block text-gray-700 font-medium mb-2">Etiketler</label>
                <input
                  type="text"
                  value={formData.tags}
                  onChange={(e) => setFormData({ ...formData, tags: e.target.value })}
                  className="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Etiketleri virgülle ayırın (örn: react, javascript, hooks)"
                />
              </div>
            </div>
            
            <div>
              <label className="block text-gray-700 font-medium mb-2">Kod</label>
              <div className="bg-gray-900 rounded-lg overflow-hidden">
                <div className="flex justify-between items-center bg-gray-800 px-4 py-2">
                  <div className="flex items-center">
                    <div 
                      className="w-3 h-3 rounded-full mr-2" 
                      style={{ backgroundColor: languageColors[formData.language] || '#6B7280' }}
                    ></div>
                    <span className="text-gray-300 text-sm">{formData.language || 'Dil seçilmedi'}</span>
                  </div>
                  <div className="flex space-x-2">
                    <button
                      type="button"
                      onClick={() => navigator.clipboard.writeText(formData.code)}
                      className="text-gray-400 hover:text-white transition-colors"
                    >
                      <Copy className="h-4 w-4" />
                    </button>
                  </div>
                </div>
                <textarea
                  value={formData.code}
                  onChange={(e) => setFormData({ ...formData, code: e.target.value })}
                  className="w-full bg-gray-900 text-gray-100 p-4 focus:outline-none resize-none font-mono text-sm"
                  rows={12}
                  placeholder="Kodunuzu buraya yazın..."
                  required
                />
              </div>
            </div>
          </form>
        </div>
        
        <div className="bg-gray-100 px-6 py-4 flex justify-end space-x-3">
          <button
            onClick={onClose}
            className="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-200 transition-colors"
          >
            İptal
          </button>
          <button
            onClick={handleSubmit}
            className="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2"
          >
            <Save className="h-4 w-4" />
            <span>{snippet ? 'Güncelle' : 'Kaydet'}</span>
          </button>
        </div>
      </div>
    </div>
  );
}

function SnippetDetailModal({ snippet, onClose, onEdit, onDelete, onToggleFavorite, onCopy }: {
  snippet: Snippet;
  onClose: () => void;
  onEdit: (snippet: Snippet) => void;
  onDelete: (id: number) => void;
  onToggleFavorite: (id: number) => void;
  onCopy: (text: string) => void;
}) {
  const handleCopy = () => {
    onCopy(snippet.code);
    // You could show a toast here
  };

  const handleDownload = () => {
    const element = document.createElement('a');
    const file = new Blob([snippet.code], { type: 'text/plain' });
    element.href = URL.createObjectURL(file);
    element.download = `${snippet.title.replace(/[^a-zA-Z0-9]/g, '_')}.${snippet.language}`;
    document.body.appendChild(element);
    element.click();
    document.body.removeChild(element);
  };

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div className="bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
        <div className="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6">
          <div className="flex justify-between items-center">
            <h3 className="text-xl font-bold flex items-center">
              <Code2 className="h-6 w-6 mr-2" />
              {snippet.title}
            </h3>
            <button
              onClick={onClose}
              className="text-white hover:text-gray-200 transition-colors"
            >
              <X className="h-6 w-6" />
            </button>
          </div>
        </div>
        
        <div className="p-6 overflow-y-auto" style={{ maxHeight: 'calc(90vh - 200px)' }}>
          <div className="space-y-6">
            <div className="flex justify-between items-start">
              <div>
                <p className="text-gray-600 mb-3">{snippet.description}</p>
                <div className="flex items-center space-x-4 text-sm text-gray-500">
                  <div className="flex items-center">
                    <div 
                      className="w-3 h-3 rounded-full mr-2" 
                      style={{ backgroundColor: languageColors[snippet.language] || '#6B7280' }}
                    ></div>
                    {snippet.language}
                  </div>
                  <div className="flex items-center">
                    <Clock className="h-4 w-4 mr-1" />
                    {new Date(snippet.created_at).toLocaleDateString('tr-TR')}
                  </div>
                  <div className="flex items-center">
                    <Eye className="h-4 w-4 mr-1" />
                    {snippet.views} görüntülenme
                  </div>
                </div>
              </div>
              <div className="flex items-center space-x-2">
                <button
                  onClick={() => onToggleFavorite(snippet.id)}
                  className={`p-2 rounded-lg transition-colors ${
                    snippet.is_favorite 
                      ? 'text-red-500 hover:text-red-600 bg-red-50' 
                      : 'text-gray-400 hover:text-red-500 hover:bg-red-50'
                  }`}
                >
                  <Heart className={`h-5 w-5 ${snippet.is_favorite ? 'fill-current' : ''}`} />
                </button>
                <button
                  onClick={() => onEdit(snippet)}
                  className="p-2 text-blue-600 hover:text-blue-700 bg-blue-50 rounded-lg transition-colors"
                >
                  <Edit className="h-5 w-5" />
                </button>
              </div>
            </div>
            
            {/* Tags */}
            {snippet.tags.length > 0 && (
              <div className="flex flex-wrap gap-2">
                {snippet.tags.map(tag => (
                  <span key={tag} className="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm flex items-center">
                    <Tag className="h-3 w-3 mr-1" />
                    {tag}
                  </span>
                ))}
              </div>
            )}
            
            {/* Code */}
            <div className="bg-gray-900 rounded-lg overflow-hidden">
              <div className="flex justify-between items-center bg-gray-800 px-4 py-3">
                <div className="flex items-center">
                  <div 
                    className="w-3 h-3 rounded-full mr-2" 
                    style={{ backgroundColor: languageColors[snippet.language] || '#6B7280' }}
                  ></div>
                  <span className="text-gray-300 text-sm">{snippet.language}</span>
                </div>
                <div className="flex space-x-2">
                  <button
                    onClick={handleCopy}
                    className="text-gray-400 hover:text-white transition-colors p-1"
                    title="Kopyala"
                  >
                    <Copy className="h-4 w-4" />
                  </button>
                  <button
                    onClick={handleDownload}
                    className="text-gray-400 hover:text-white transition-colors p-1"
                    title="İndir"
                  >
                    <Download className="h-4 w-4" />
                  </button>
                </div>
              </div>
              <pre className="text-gray-100 p-4 overflow-x-auto text-sm">
                <code>{snippet.code}</code>
              </pre>
            </div>
          </div>
        </div>
        
        <div className="bg-gray-100 px-6 py-4 flex justify-between items-center">
          <div className="flex items-center space-x-4 text-gray-600">
            <div className="flex items-center">
              <Eye className="h-4 w-4 mr-1" />
              <span>{snippet.views} görüntülenme</span>
            </div>
          </div>
          <div className="flex space-x-3">
            <button
              onClick={() => {
                if (navigator.share) {
                  navigator.share({
                    title: snippet.title,
                    text: snippet.description,
                    url: window.location.href
                  });
                }
              }}
              className="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-200 transition-colors flex items-center space-x-2"
            >
              <Share className="h-4 w-4" />
              <span>Paylaş</span>
            </button>
            <button
              onClick={handleDownload}
              className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2"
            >
              <Download className="h-4 w-4" />
              <span>Dışa Aktar</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

function AuthPage({ onLogin }: { onLogin: () => void }) {
  const [isLogin, setIsLogin] = useState(true);
  const [formData, setFormData] = useState({
    username: '',
    email: '',
    password: '',
    firstName: '',
    lastName: ''
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onLogin();
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 flex items-center justify-center p-4">
      <div className="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
        <div className="bg-gradient-to-r from-blue-600 to-purple-600 p-8 text-center">
          <div className="bg-white bg-opacity-20 p-3 rounded-full w-16 h-16 mx-auto mb-4">
            <Code2 className="h-10 w-10 text-white" />
          </div>
          <h1 className="text-2xl font-bold text-white">Snippet Manager</h1>
          <p className="text-blue-100 mt-2">Kod parçacıklarınızı organize edin</p>
        </div>
        
        <div className="p-8">
          {/* Tab Navigation */}
          <div className="flex bg-gray-100 rounded-lg p-1 mb-6">
            <button
              onClick={() => setIsLogin(true)}
              className={`flex-1 py-2 px-4 rounded-md text-sm font-medium transition-colors ${
                isLogin 
                  ? 'bg-white text-blue-600 shadow-sm' 
                  : 'text-gray-600 hover:text-blue-600'
              }`}
            >
              Giriş Yap
            </button>
            <button
              onClick={() => setIsLogin(false)}
              className={`flex-1 py-2 px-4 rounded-md text-sm font-medium transition-colors ${
                !isLogin 
                  ? 'bg-white text-blue-600 shadow-sm' 
                  : 'text-gray-600 hover:text-blue-600'
              }`}
            >
              Kayıt Ol
            </button>
          </div>

          <form onSubmit={handleSubmit} className="space-y-4">
            {!isLogin && (
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-gray-700 font-medium mb-1">Ad</label>
                  <input
                    type="text"
                    value={formData.firstName}
                    onChange={(e) => setFormData({ ...formData, firstName: e.target.value })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    required={!isLogin}
                  />
                </div>
                <div>
                  <label className="block text-gray-700 font-medium mb-1">Soyad</label>
                  <input
                    type="text"
                    value={formData.lastName}
                    onChange={(e) => setFormData({ ...formData, lastName: e.target.value })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    required={!isLogin}
                  />
                </div>
              </div>
            )}

            <div>
              <label className="block text-gray-700 font-medium mb-1">
                {isLogin ? 'Kullanıcı Adı veya E-posta' : 'Kullanıcı Adı'}
              </label>
              <input
                type="text"
                value={formData.username}
                onChange={(e) => setFormData({ ...formData, username: e.target.value })}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                required
              />
            </div>

            {!isLogin && (
              <div>
                <label className="block text-gray-700 font-medium mb-1">E-posta</label>
                <input
                  type="email"
                  value={formData.email}
                  onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  required={!isLogin}
                />
              </div>
            )}

            <div>
              <label className="block text-gray-700 font-medium mb-1">Şifre</label>
              <input
                type="password"
                value={formData.password}
                onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                required
              />
            </div>

            {isLogin && (
              <div className="flex items-center">
                <input type="checkbox" id="remember" className="mr-2" />
                <label htmlFor="remember" className="text-gray-600 text-sm">Beni hatırla</label>
              </div>
            )}

            <button
              type="submit"
              className="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 font-medium"
            >
              {isLogin ? 'Giriş Yap' : 'Kayıt Ol'}
            </button>
          </form>
        </div>
      </div>
    </div>
  );
}

export default App;