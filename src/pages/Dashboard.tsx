import React from 'react';
import { Link } from 'react-router-dom';
import { 
  Package, 
  TrendingUp, 
  AlertTriangle, 
  CheckCircle,
  Clock,
  DollarSign,
  Users,
  FileText,
  Settings
} from 'lucide-react';

const Dashboard = () => {
  const stats = [
    {
      name: 'Lots en traitement',
      value: '12',
      change: '+2',
      changeType: 'increase',
      icon: Clock,
      color: 'text-orange-600',
      bgColor: 'bg-orange-50'
    },
    {
      name: 'Produits traités ce mois',
      value: '1,247',
      change: '+12%',
      changeType: 'increase',
      icon: CheckCircle,
      color: 'text-green-600',
      bgColor: 'bg-green-50'
    },
    {
      name: 'Valeur des achats',
      value: '€45,230',
      change: '+8%',
      changeType: 'increase',
      icon: DollarSign,
      color: 'text-blue-600',
      bgColor: 'bg-blue-50'
    },
    {
      name: 'Taux de fonctionnalité',
      value: '87%',
      change: '-2%',
      changeType: 'decrease',
      icon: TrendingUp,
      color: 'text-purple-600',
      bgColor: 'bg-purple-50'
    }
  ];

  const recentLots = [
    {
      id: 'LOT-2025-01-15-001',
      name: 'Achat Fournisseur SYA - 15/01/2025',
      supplier: 'SYA Electronics',
      status: 'En cours',
      products: 45,
      value: '€12,450'
    },
    {
      id: 'LOT-2025-01-14-003',
      name: 'Lot iPhone Mix - 14/01/2025',
      supplier: 'TechSource Pro',
      status: 'Traité',
      products: 23,
      value: '€8,970'
    },
    {
      id: 'LOT-2025-01-14-002',
      name: 'Samsung Galaxy Lot',
      supplier: 'Mobile Discount',
      status: 'En attente',
      products: 67,
      value: '€15,680'
    }
  ];

  const topDefects = [
    { name: 'Écran cassé', count: 34, percentage: 28 },
    { name: 'Batterie HS', count: 27, percentage: 22 },
    { name: 'Ne s\'allume pas', count: 19, percentage: 16 },
    { name: 'Problème caméra', count: 15, percentage: 12 },
    { name: 'Bouton défaillant', count: 12, percentage: 10 }
  ];

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Tableau de Bord</h1>
          <p className="text-gray-600">Vue d'ensemble de votre activité de gestion des lots</p>
        </div>
        <Link
          to="/create-lot"
          className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center"
        >
          <Package className="w-4 h-4 mr-2" />
          Nouveau Lot
        </Link>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {stats.map((stat) => {
          const Icon = stat.icon;
          return (
            <div key={stat.name} className="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-gray-600">{stat.name}</p>
                  <p className="text-2xl font-bold text-gray-900 mt-2">{stat.value}</p>
                  <div className="flex items-center mt-2">
                    <span className={`text-sm font-medium ${
                      stat.changeType === 'increase' ? 'text-green-600' : 'text-red-600'
                    }`}>
                      {stat.change}
                    </span>
                    <span className="text-sm text-gray-500 ml-1">vs mois dernier</span>
                  </div>
                </div>
                <div className={`p-3 rounded-lg ${stat.bgColor}`}>
                  <Icon className={`w-6 h-6 ${stat.color}`} />
                </div>
              </div>
            </div>
          );
        })}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Recent Lots */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-200">
          <div className="p-6 border-b border-gray-200">
            <div className="flex items-center justify-between">
              <h2 className="text-lg font-semibold text-gray-900">Lots Récents</h2>
              <Link to="/lots" className="text-blue-600 hover:text-blue-700 text-sm font-medium">
                Voir tous
              </Link>
            </div>
          </div>
          <div className="divide-y divide-gray-200">
            {recentLots.map((lot) => (
              <div key={lot.id} className="p-6 hover:bg-gray-50 transition-colors">
                <div className="flex items-center justify-between">
                  <div className="flex-1">
                    <div className="flex items-center space-x-3">
                      <h3 className="text-sm font-medium text-gray-900">{lot.name}</h3>
                      <span className={`px-2 py-1 text-xs font-medium rounded-full ${
                        lot.status === 'Traité' 
                          ? 'bg-green-100 text-green-800'
                          : lot.status === 'En cours'
                          ? 'bg-orange-100 text-orange-800'
                          : 'bg-gray-100 text-gray-800'
                      }`}>
                        {lot.status}
                      </span>
                    </div>
                    <p className="text-sm text-gray-500 mt-1">{lot.supplier}</p>
                    <div className="flex items-center space-x-4 mt-2">
                      <span className="text-sm text-gray-600">{lot.products} produits</span>
                      <span className="text-sm font-medium text-gray-900">{lot.value}</span>
                    </div>
                  </div>
                  <Link
                    to={`/lots/${lot.id}`}
                    className="text-blue-600 hover:text-blue-700 text-sm font-medium"
                  >
                    Voir détails
                  </Link>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Top Defects */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-200">
          <div className="p-6 border-b border-gray-200">
            <h2 className="text-lg font-semibold text-gray-900">Pannes les Plus Fréquentes</h2>
          </div>
          <div className="p-6">
            <div className="space-y-4">
              {topDefects.map((defect, index) => (
                <div key={defect.name} className="flex items-center">
                  <div className="flex-1">
                    <div className="flex items-center justify-between mb-1">
                      <span className="text-sm font-medium text-gray-900">{defect.name}</span>
                      <span className="text-sm text-gray-500">{defect.count}</span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-2">
                      <div
                        className="bg-red-500 h-2 rounded-full"
                        style={{ width: `${defect.percentage}%` }}
                      ></div>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>

      {/* Quick Actions */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 className="text-lg font-semibold text-gray-900 mb-4">Actions Rapides</h2>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <Link
            to="/create-lot"
            className="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <Package className="w-8 h-8 text-blue-600 mr-3" />
            <div>
              <h3 className="font-medium text-gray-900">Créer un Nouveau Lot</h3>
              <p className="text-sm text-gray-500">Importer des produits depuis un fichier</p>
            </div>
          </Link>
          
          <Link
            to="/statistics"
            className="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <TrendingUp className="w-8 h-8 text-green-600 mr-3" />
            <div>
              <h3 className="font-medium text-gray-900">Voir les Statistiques</h3>
              <p className="text-sm text-gray-500">Analyser les performances</p>
            </div>
          </Link>
          
          <Link
            to="/configuration"
            className="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <Settings className="w-8 h-8 text-purple-600 mr-3" />
            <div>
              <h3 className="font-medium text-gray-900">Configuration</h3>
              <p className="text-sm text-gray-500">Gérer fournisseurs et pannes</p>
            </div>
          </Link>
        </div>
      </div>
    </div>
  );
};

export default Dashboard;