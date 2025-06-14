import React, { useState } from 'react';
import { 
  Users, 
  AlertTriangle, 
  Plus, 
  Edit, 
  Trash2, 
  Save,
  X,
  Settings,
  BookOpen
} from 'lucide-react';

const Configuration = () => {
  const [activeTab, setActiveTab] = useState('suppliers');
  const [isAddingSupplier, setIsAddingSupplier] = useState(false);
  const [isAddingDefect, setIsAddingDefect] = useState(false);
  const [newSupplier, setNewSupplier] = useState({ name: '', contact: '', email: '', phone: '' });
  const [newDefect, setNewDefect] = useState('');

  const [suppliers, setSuppliers] = useState([
    {
      id: 1,
      name: 'SYA Electronics',
      contact: 'Jean Dupont',
      email: 'contact@sya-electronics.com',
      phone: '+33 1 23 45 67 89',
      totalLots: 12,
      averageRate: 87.8
    },
    {
      id: 2,
      name: 'TechSource Pro',
      contact: 'Marie Martin',
      email: 'orders@techsource.fr',
      phone: '+33 1 98 76 54 32',
      totalLots: 8,
      averageRate: 91.5
    },
    {
      id: 3,
      name: 'Mobile Discount',
      contact: 'Pierre Leroy',
      email: 'achat@mobilediscount.fr',
      phone: '+33 1 11 22 33 44',
      totalLots: 15,
      averageRate: 79.6
    }
  ]);

  const [defects, setDefects] = useState([
    { id: 1, name: 'Écran cassé', frequency: 127, active: true },
    { id: 2, name: 'Batterie HS', frequency: 89, active: true },
    { id: 3, name: 'Ne s\'allume pas', frequency: 67, active: true },
    { id: 4, name: 'Problème caméra', frequency: 45, active: true },
    { id: 5, name: 'Bouton défaillant', frequency: 38, active: true },
    { id: 6, name: 'Problème audio', frequency: 28, active: true },
    { id: 7, name: 'Rayures importantes', frequency: 13, active: true },
    { id: 8, name: 'Oxydation', frequency: 8, active: false }
  ]);

  const [dictionary, setDictionary] = useState([
    { id: 1, pattern: 'Go, gb, giga', replacement: 'GB', category: 'Stockage' },
    { id: 2, pattern: 'Black, noir, jet black', replacement: 'Noir', category: 'Couleur' },
    { id: 3, pattern: 'Space Gray, Gris Sidéral', replacement: 'Gris Sidéral', category: 'Couleur' },
    { id: 4, pattern: 'Blue, bleu, ocean blue', replacement: 'Bleu', category: 'Couleur' },
    { id: 5, pattern: 'Grade A, excellent, parfait', replacement: 'Grade A', category: 'État' },
    { id: 6, pattern: 'Grade B, bon, good', replacement: 'Grade B', category: 'État' }
  ]);

  const handleAddSupplier = () => {
    if (newSupplier.name.trim()) {
      setSuppliers([...suppliers, {
        id: Date.now(),
        ...newSupplier,
        totalLots: 0,
        averageRate: 0
      }]);
      setNewSupplier({ name: '', contact: '', email: '', phone: '' });
      setIsAddingSupplier(false);
    }
  };

  const handleAddDefect = () => {
    if (newDefect.trim()) {
      setDefects([...defects, {
        id: Date.now(),
        name: newDefect,
        frequency: 0,
        active: true
      }]);
      setNewDefect('');
      setIsAddingDefect(false);
    }
  };

  const toggleDefectStatus = (id: number) => {
    setDefects(defects.map(defect =>
      defect.id === id ? { ...defect, active: !defect.active } : defect
    ));
  };

  const removeSupplier = (id: number) => {
    setSuppliers(suppliers.filter(supplier => supplier.id !== id));
  };

  const removeDefect = (id: number) => {
    setDefects(defects.filter(defect => defect.id !== id));
  };

  const tabs = [
    { id: 'suppliers', name: 'Fournisseurs', icon: Users },
    { id: 'defects', name: 'Pannes', icon: AlertTriangle },
    { id: 'dictionary', name: 'Dictionnaire', icon: BookOpen }
  ];

  return (
    <div className="space-y-6">
      {/* Header */}
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Configuration</h1>
        <p className="text-gray-600">Gérer les fournisseurs, pannes et règles de correspondance</p>
      </div>

      {/* Tabs */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-200">
        <div className="border-b border-gray-200">
          <nav className="flex space-x-8 px-6" aria-label="Tabs">
            {tabs.map((tab) => {
              const Icon = tab.icon;
              return (
                <button
                  key={tab.id}
                  onClick={() => setActiveTab(tab.id)}
                  className={`flex items-center py-4 px-1 text-sm font-medium border-b-2 ${
                    activeTab === tab.id
                      ? 'border-blue-500 text-blue-600'
                      : 'border-transparent text-gray-500 hover:text-gray-700'
                  }`}
                >
                  <Icon className="w-4 h-4 mr-2" />
                  {tab.name}
                </button>
              );
            })}
          </nav>
        </div>

        <div className="p-6">
          {/* Suppliers Tab */}
          {activeTab === 'suppliers' && (
            <div className="space-y-6">
              <div className="flex justify-between items-center">
                <h2 className="text-lg font-semibold text-gray-900">Gestion des Fournisseurs</h2>
                <button
                  onClick={() => setIsAddingSupplier(true)}
                  className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center"
                >
                  <Plus className="w-4 h-4 mr-2" />
                  Ajouter Fournisseur
                </button>
              </div>

              {/* Add Supplier Form */}
              {isAddingSupplier && (
                <div className="bg-gray-50 rounded-lg p-4 border border-gray-200">
                  <h3 className="font-medium text-gray-900 mb-4">Nouveau Fournisseur</h3>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input
                      type="text"
                      placeholder="Nom du fournisseur *"
                      value={newSupplier.name}
                      onChange={(e) => setNewSupplier({ ...newSupplier, name: e.target.value })}
                      className="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                    <input
                      type="text"
                      placeholder="Contact principal"
                      value={newSupplier.contact}
                      onChange={(e) => setNewSupplier({ ...newSupplier, contact: e.target.value })}
                      className="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                    <input
                      type="email"
                      placeholder="Email"
                      value={newSupplier.email}
                      onChange={(e) => setNewSupplier({ ...newSupplier, email: e.target.value })}
                      className="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                    <input
                      type="tel"
                      placeholder="Téléphone"
                      value={newSupplier.phone}
                      onChange={(e) => setNewSupplier({ ...newSupplier, phone: e.target.value })}
                      className="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>
                  <div className="flex justify-end space-x-3 mt-4">
                    <button
                      onClick={() => setIsAddingSupplier(false)}
                      className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                      Annuler
                    </button>
                    <button
                      onClick={handleAddSupplier}
                      className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center"
                    >
                      <Save className="w-4 h-4 mr-2" />
                      Ajouter
                    </button>
                  </div>
                </div>
              )}

              {/* Suppliers Table */}
              <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200">
                  <thead className="bg-gray-50">
                    <tr>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Fournisseur
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Contact
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Statistiques
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                      </th>
                    </tr>
                  </thead>
                  <tbody className="bg-white divide-y divide-gray-200">
                    {suppliers.map((supplier) => (
                      <tr key={supplier.id} className="hover:bg-gray-50">
                        <td className="px-6 py-4 whitespace-nowrap">
                          <div>
                            <div className="text-sm font-medium text-gray-900">{supplier.name}</div>
                          </div>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          <div className="text-sm text-gray-900">{supplier.contact}</div>
                          <div className="text-sm text-gray-500">{supplier.email}</div>
                          <div className="text-sm text-gray-500">{supplier.phone}</div>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          <div className="text-sm text-gray-900">{supplier.totalLots} lots</div>
                          <div className={`text-sm ${
                            supplier.averageRate >= 85 ? 'text-green-600' : 
                            supplier.averageRate >= 80 ? 'text-orange-600' : 'text-red-600'
                          }`}>
                            {supplier.averageRate}% fonctionnel
                          </div>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                          <div className="flex items-center space-x-2">
                            <button className="text-blue-600 hover:text-blue-700">
                              <Edit className="w-4 h-4" />
                            </button>
                            <button
                              onClick={() => removeSupplier(supplier.id)}
                              className="text-red-600 hover:text-red-700"
                            >
                              <Trash2 className="w-4 h-4" />
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}

          {/* Defects Tab */}
          {activeTab === 'defects' && (
            <div className="space-y-6">
              <div className="flex justify-between items-center">
                <h2 className="text-lg font-semibold text-gray-900">Gestion des Pannes</h2>
                <button
                  onClick={() => setIsAddingDefect(true)}
                  className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center"
                >
                  <Plus className="w-4 h-4 mr-2" />
                  Ajouter Panne
                </button>
              </div>

              {/* Add Defect Form */}
              {isAddingDefect && (
                <div className="bg-gray-50 rounded-lg p-4 border border-gray-200">
                  <h3 className="font-medium text-gray-900 mb-4">Nouvelle Panne</h3>
                  <div className="flex space-x-4">
                    <input
                      type="text"
                      placeholder="Nom de la panne"
                      value={newDefect}
                      onChange={(e) => setNewDefect(e.target.value)}
                      className="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                    <button
                      onClick={handleAddDefect}
                      className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center"
                    >
                      <Save className="w-4 h-4 mr-2" />
                      Ajouter
                    </button>
                    <button
                      onClick={() => setIsAddingDefect(false)}
                      className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                      <X className="w-4 h-4" />
                    </button>
                  </div>
                </div>
              )}

              {/* Defects Grid */}
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {defects.map((defect) => (
                  <div key={defect.id} className={`p-4 rounded-lg border-2 transition-colors ${
                    defect.active ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-gray-50'
                  }`}>
                    <div className="flex items-center justify-between mb-2">
                      <h3 className="font-medium text-gray-900">{defect.name}</h3>
                      <div className="flex items-center space-x-2">
                        <button
                          onClick={() => toggleDefectStatus(defect.id)}
                          className={`w-8 h-4 rounded-full transition-colors ${
                            defect.active ? 'bg-green-600' : 'bg-gray-300'
                          }`}
                        >
                          <div className={`w-3 h-3 bg-white rounded-full transition-transform ${
                            defect.active ? 'translate-x-4' : 'translate-x-0.5'
                          }`}></div>
                        </button>
                        <button
                          onClick={() => removeDefect(defect.id)}
                          className="text-red-600 hover:text-red-700"
                        >
                          <Trash2 className="w-4 h-4" />
                        </button>
                      </div>
                    </div>
                    <div className="text-sm text-gray-600">
                      Fréquence: {defect.frequency} occurrences
                    </div>
                    <div className={`text-xs mt-1 ${defect.active ? 'text-green-600' : 'text-gray-500'}`}>
                      {defect.active ? 'Actif' : 'Inactif'}
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Dictionary Tab */}
          {activeTab === 'dictionary' && (
            <div className="space-y-6">
              <div className="flex justify-between items-center">
                <div>
                  <h2 className="text-lg font-semibold text-gray-900">Dictionnaire de Correspondances</h2>
                  <p className="text-sm text-gray-600 mt-1">
                    Définissez les règles de normalisation pour l'interprétation automatique des noms de produits
                  </p>
                </div>
                <button className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center">
                  <Plus className="w-4 h-4 mr-2" />
                  Ajouter Règle
                </button>
              </div>

              {/* Dictionary Table */}
              <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200">
                  <thead className="bg-gray-50">
                    <tr>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Catégorie
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Variations Détectées
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Valeur Normalisée
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                      </th>
                    </tr>
                  </thead>
                  <tbody className="bg-white divide-y divide-gray-200">
                    {dictionary.map((rule) => (
                      <tr key={rule.id} className="hover:bg-gray-50">
                        <td className="px-6 py-4 whitespace-nowrap">
                          <span className="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                            {rule.category}
                          </span>
                        </td>
                        <td className="px-6 py-4">
                          <div className="text-sm text-gray-900 max-w-xs">
                            {rule.pattern}
                          </div>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          <span className="text-sm font-medium text-green-600">
                            {rule.replacement}
                          </span>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                          <div className="flex items-center space-x-2">
                            <button className="text-blue-600 hover:text-blue-700">
                              <Edit className="w-4 h-4" />
                            </button>
                            <button className="text-red-600 hover:text-red-700">
                              <Trash2 className="w-4 h-4" />
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>

              {/* Dictionary Help */}
              <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div className="flex items-start">
                  <Settings className="w-5 h-5 text-blue-600 mr-2 mt-0.5" />
                  <div className="text-sm">
                    <p className="font-medium text-blue-900">Comment utiliser le dictionnaire</p>
                    <ul className="text-blue-700 mt-2 space-y-1">
                      <li>• Séparez les variations par des virgules (ex: "Go, gb, giga")</li>
                      <li>• La valeur normalisée sera utilisée dans les suggestions de produits</li>
                      <li>• Les règles sont appliquées automatiquement lors de l'import</li>
                      <li>• Organisez par catégories pour une meilleure maintenance</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default Configuration;