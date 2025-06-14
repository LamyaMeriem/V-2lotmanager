import React, { useState } from 'react';
import { useParams, useLocation } from 'react-router-dom';
import { 
  CheckCircle, 
  XCircle, 
  Search, 
  Edit, 
  Save,
  AlertTriangle,
  Package,
  Smartphone,
  Filter,
  BarChart3
} from 'lucide-react';

const ProductQualification = () => {
  const { id } = useParams();
  const location = useLocation();
  const { lotData } = location.state || {};

  const [filter, setFilter] = useState('all');
  const [searchTerm, setSearchTerm] = useState('');

  // Simulated products from import
  const [products, setProducts] = useState([
    {
      id: 1,
      rawName: 'iPhone 11 64GB Black Grade B',
      serialNumber: '351234567890123',
      unitPrice: 450,
      status: 'pending', // pending, functional, defective
      selectedSku: '',
      defects: [],
      suggestedProducts: []
    },
    {
      id: 2,
      rawName: 'Samsung Galaxy S20 128GB Blue',
      serialNumber: '351987654321098',
      unitPrice: 380,
      status: 'pending',
      selectedSku: '',
      defects: [],
      suggestedProducts: []
    },
    {
      id: 3,
      rawName: 'iPhone 12 Pro 256GB Space Gray',
      serialNumber: '351456789012345',
      unitPrice: 680,
      status: 'functional',
      selectedSku: 'IPHONE-12-PRO-256-GRAY-A',
      defects: [],
      suggestedProducts: []
    },
    {
      id: 4,
      rawName: 'Samsung Galaxy S21 écran cassé',
      serialNumber: '351789012345678',
      unitPrice: 200,
      status: 'defective',
      selectedSku: '',
      defects: ['Écran cassé'],
      suggestedProducts: []
    }
  ]);

  const availableDefects = [
    'Écran cassé',
    'Batterie HS',
    'Ne s\'allume pas',
    'Problème caméra',
    'Bouton défaillant',
    'Problème audio',
    'Rayures importantes',
    'Oxydation'
  ];

  const suggestedSkus = {
    'iPhone 11': [
      { sku: 'IPHONE-11-64-BLACK-A', name: '64GB - Noir - Grade A', price: 599 },
      { sku: 'IPHONE-11-64-BLACK-B', name: '64GB - Noir - Grade B', price: 549 },
      { sku: 'IPHONE-11-128-BLACK-A', name: '128GB - Noir - Grade A', price: 649 }
    ],
    'Samsung Galaxy S20': [
      { sku: 'SAMSUNG-S20-128-BLUE-A', name: '128GB - Bleu - Grade A', price: 499 },
      { sku: 'SAMSUNG-S20-128-BLUE-B', name: '128GB - Bleu - Grade B', price: 449 }
    ]
  };

  const handleStatusChange = (productId: number, status: string) => {
    setProducts(products.map(product => 
      product.id === productId 
        ? { 
            ...product, 
            status,
            selectedSku: status === 'defective' ? '' : product.selectedSku,
            defects: status === 'functional' ? [] : product.defects
          }
        : product
    ));
  };

  const handleSkuSelection = (productId: number, sku: string) => {
    setProducts(products.map(product => 
      product.id === productId 
        ? { ...product, selectedSku: sku }
        : product
    ));
  };

  const handleDefectToggle = (productId: number, defect: string) => {
    setProducts(products.map(product => 
      product.id === productId 
        ? {
            ...product,
            defects: product.defects.includes(defect)
              ? product.defects.filter(d => d !== defect)
              : [...product.defects, defect]
          }
        : product
    ));
  };

  const getProductSuggestions = (rawName: string) => {
    if (rawName.toLowerCase().includes('iphone 11')) {
      return suggestedSkus['iPhone 11'] || [];
    }
    if (rawName.toLowerCase().includes('galaxy s20')) {
      return suggestedSkus['Samsung Galaxy S20'] || [];
    }
    return [];
  };

  const filteredProducts = products.filter(product => {
    const matchesFilter = filter === 'all' || product.status === filter;
    const matchesSearch = product.rawName.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         product.serialNumber.toLowerCase().includes(searchTerm.toLowerCase());
    return matchesFilter && matchesSearch;
  });

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'functional':
        return <CheckCircle className="w-5 h-5 text-green-600" />;
      case 'defective':
        return <XCircle className="w-5 h-5 text-red-600" />;
      default:
        return <AlertTriangle className="w-5 h-5 text-orange-600" />;
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'functional':
        return 'bg-green-100 text-green-800';
      case 'defective':
        return 'bg-red-100 text-red-800';
      default:
        return 'bg-orange-100 text-orange-800';
    }
  };

  const getStatusText = (status: string) => {
    switch (status) {
      case 'functional':
        return 'Fonctionnel';
      case 'defective':
        return 'Défectueux';
      default:
        return 'En attente';
    }
  };

  const stats = {
    total: products.length,
    pending: products.filter(p => p.status === 'pending').length,
    functional: products.filter(p => p.status === 'functional').length,
    defective: products.filter(p => p.status === 'defective').length
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-start">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Qualification des Produits</h1>
          <p className="text-gray-600 mt-2">
            Lot: {lotData?.name || `Lot ${id}`}
          </p>
        </div>
        <button className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center">
          <Save className="w-4 h-4 mr-2" />
          Sauvegarder
        </button>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div className="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
          <div className="flex items-center">
            <Package className="w-8 h-8 text-blue-600 mr-3" />
            <div>
              <p className="text-2xl font-bold text-gray-900">{stats.total}</p>
              <p className="text-sm text-gray-500">Total produits</p>
            </div>
          </div>
        </div>
        <div className="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
          <div className="flex items-center">
            <AlertTriangle className="w-8 h-8 text-orange-600 mr-3" />
            <div>
              <p className="text-2xl font-bold text-gray-900">{stats.pending}</p>
              <p className="text-sm text-gray-500">En attente</p>
            </div>
          </div>
        </div>
        <div className="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
          <div className="flex items-center">
            <CheckCircle className="w-8 h-8 text-green-600 mr-3" />
            <div>
              <p className="text-2xl font-bold text-gray-900">{stats.functional}</p>
              <p className="text-sm text-gray-500">Fonctionnels</p>
            </div>
          </div>
        </div>
        <div className="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
          <div className="flex items-center">
            <XCircle className="w-8 h-8 text-red-600 mr-3" />
            <div>
              <p className="text-2xl font-bold text-gray-900">{stats.defective}</p>
              <p className="text-sm text-gray-500">Défectueux</p>
            </div>
          </div>
        </div>
      </div>

      {/* Filters */}
      <div className="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <div className="flex flex-col sm:flex-row gap-4">
          <div className="flex-1 relative">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
            <input
              type="text"
              placeholder="Rechercher par nom ou numéro de série..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div className="flex items-center space-x-2">
            <Filter className="w-4 h-4 text-gray-400" />
            <select
              value={filter}
              onChange={(e) => setFilter(e.target.value)}
              className="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="all">Tous les statuts</option>
              <option value="pending">En attente</option>
              <option value="functional">Fonctionnels</option>
              <option value="defective">Défectueux</option>
            </select>
          </div>
        </div>
      </div>

      {/* Products List */}
      <div className="space-y-4">
        {filteredProducts.map((product) => (
          <div key={product.id} className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div className="flex items-start justify-between mb-4">
              <div className="flex-1">
                <div className="flex items-center space-x-3 mb-2">
                  <Smartphone className="w-5 h-5 text-gray-400" />
                  <h3 className="text-lg font-medium text-gray-900">{product.rawName}</h3>
                  <span className={`px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(product.status)}`}>
                    {getStatusText(product.status)}
                  </span>
                </div>
                <div className="text-sm text-gray-500 space-y-1">
                  <p>Numéro de série: {product.serialNumber}</p>
                  <p>Prix d'achat: €{product.unitPrice}</p>
                </div>
              </div>
              <div className="flex items-center space-x-2">
                <Edit className="w-4 h-4 text-gray-400" />
              </div>
            </div>

            <div className="border-t border-gray-200 pt-4">
              {/* Status Selection */}
              <div className="mb-4">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Statut de Test
                </label>
                <div className="flex space-x-4">
                  <button
                    onClick={() => handleStatusChange(product.id, 'functional')}
                    className={`flex items-center px-4 py-2 rounded-lg border transition-colors ${
                      product.status === 'functional'
                        ? 'border-green-500 bg-green-50 text-green-700'
                        : 'border-gray-300 hover:bg-gray-50'
                    }`}
                  >
                    <CheckCircle className="w-4 h-4 mr-2" />
                    Fonctionnel
                  </button>
                  <button
                    onClick={() => handleStatusChange(product.id, 'defective')}
                    className={`flex items-center px-4 py-2 rounded-lg border transition-colors ${
                      product.status === 'defective'
                        ? 'border-red-500 bg-red-50 text-red-700'
                        : 'border-gray-300 hover:bg-gray-50'
                    }`}
                  >
                    <XCircle className="w-4 h-4 mr-2" />
                    Défectueux
                  </button>
                </div>
              </div>

              {/* SKU Selection for Functional Products */}
              {product.status === 'functional' && (
                <div className="mb-4">
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Sélection du SKU
                  </label>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <input
                        type="text"
                        placeholder="Rechercher un produit..."
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      />
                    </div>
                    <select
                      value={product.selectedSku}
                      onChange={(e) => handleSkuSelection(product.id, e.target.value)}
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                      <option value="">Sélectionner un SKU</option>
                      {getProductSuggestions(product.rawName).map((sku) => (
                        <option key={sku.sku} value={sku.sku}>
                          {sku.name} - €{sku.price}
                        </option>
                      ))}
                    </select>
                  </div>
                </div>
              )}

              {/* Defects Selection for Defective Products */}
              {product.status === 'defective' && (
                <div className="mb-4">
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Pannes Constatées
                  </label>
                  <div className="grid grid-cols-2 md:grid-cols-4 gap-2">
                    {availableDefects.map((defect) => (
                      <label key={defect} className="flex items-center">
                        <input
                          type="checkbox"
                          checked={product.defects.includes(defect)}
                          onChange={() => handleDefectToggle(product.id, defect)}
                          className="w-4 h-4 text-red-600 rounded focus:ring-red-500"
                        />
                        <span className="ml-2 text-sm text-gray-700">{defect}</span>
                      </label>
                    ))}
                  </div>
                </div>
              )}
            </div>
          </div>
        ))}
      </div>

      {/* Progress Summary */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div className="flex items-center justify-between mb-4">
          <h3 className="text-lg font-semibold text-gray-900">Progression</h3>
          <span className="text-sm text-gray-500">
            {stats.functional + stats.defective} / {stats.total} traités
          </span>
        </div>
        <div className="w-full bg-gray-200 rounded-full h-2">
          <div
            className="bg-blue-600 h-2 rounded-full"
            style={{ width: `${((stats.functional + stats.defective) / stats.total) * 100}%` }}
          ></div>
        </div>
      </div>
    </div>
  );
};

export default ProductQualification;