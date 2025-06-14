import React, { useState } from 'react';
import { 
  BarChart3, 
  TrendingUp, 
  TrendingDown,
  Package,
  DollarSign,
  Users,
  AlertTriangle,
  Calendar,
  Filter
} from 'lucide-react';

const Statistics = () => {
  const [timeRange, setTimeRange] = useState('30d');
  const [selectedSupplier, setSelectedSupplier] = useState('all');

  const kpis = [
    {
      name: 'Produits Achetés',
      value: '1,247',
      change: '+12%',
      changeType: 'increase',
      icon: Package,
      color: 'text-blue-600',
      bgColor: 'bg-blue-50'
    },
    {
      name: 'Coût Total des Achats',
      value: '€186,450',
      change: '+8%',
      changeType: 'increase',
      icon: DollarSign,
      color: 'text-red-600',
      bgColor: 'bg-red-50'
    },
    {
      name: 'Valeur de Revente',
      value: '€279,675',
      change: '+15%',
      changeType: 'increase',
      icon: TrendingUp,
      color: 'text-green-600',
      bgColor: 'bg-green-50'
    },
    {
      name: 'Taux de Fonctionnalité',
      value: '84.3%',
      change: '-1.2%',
      changeType: 'decrease',
      icon: BarChart3,
      color: 'text-purple-600',
      bgColor: 'bg-purple-50'
    }
  ];

  const topDefects = [
    { name: 'Écran cassé', count: 127, percentage: 31.2, trend: '+5%' },
    { name: 'Batterie HS', count: 89, percentage: 21.9, trend: '-2%' },
    { name: 'Ne s\'allume pas', count: 67, percentage: 16.5, trend: '+8%' },
    { name: 'Problème caméra', count: 45, percentage: 11.1, trend: '+12%' },
    { name: 'Bouton défaillant', count: 38, percentage: 9.3, trend: '-3%' },
    { name: 'Problème audio', count: 28, percentage: 6.9, trend: '+2%' },
    { name: 'Rayures importantes', count: 13, percentage: 3.2, trend: '-1%' }
  ];

  const supplierStats = [
    {
      name: 'SYA Electronics',
      totalProducts: 245,
      functionalRate: 87.8,
      totalValue: 45230,
      avgMargin: 35.2,
      trend: 'up'
    },
    {
      name: 'TechSource Pro',
      totalProducts: 189,
      functionalRate: 91.5,
      totalValue: 38960,
      avgMargin: 28.7,
      trend: 'up'
    },
    {
      name: 'Mobile Discount',
      totalProducts: 167,
      functionalRate: 79.6,
      totalValue: 31840,
      avgMargin: 32.1,
      trend: 'down'
    },
    {
      name: 'Digital Supply',
      totalProducts: 134,
      functionalRate: 85.1,
      totalValue: 28750,
      avgMargin: 30.8,
      trend: 'up'
    },
    {
      name: 'RefurbTech',
      totalProducts: 123,
      functionalRate: 82.9,
      totalValue: 25640,
      avgMargin: 27.4,
      trend: 'stable'
    }
  ];

  const monthlyData = [
    { month: 'Oct', products: 89, cost: 15630, revenue: 23445 },
    { month: 'Nov', products: 156, cost: 28940, revenue: 43410 },
    { month: 'Déc', products: 203, cost: 37820, revenue: 56730 },
    { month: 'Jan', products: 267, cost: 48950, revenue: 73425 }
  ];

  const getTrendIcon = (trend: string) => {
    switch (trend) {
      case 'up':
        return <TrendingUp className="w-4 h-4 text-green-600" />;
      case 'down':
        return <TrendingDown className="w-4 h-4 text-red-600" />;
      default:
        return <div className="w-4 h-4 bg-gray-400 rounded-full"></div>;
    }
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Statistiques et Rapports</h1>
          <p className="text-gray-600">Analyse des performances et indicateurs clés</p>
        </div>
        
        <div className="flex items-center space-x-4">
          <div className="flex items-center space-x-2">
            <Calendar className="w-4 h-4 text-gray-400" />
            <select
              value={timeRange}
              onChange={(e) => setTimeRange(e.target.value)}
              className="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="7d">7 derniers jours</option>
              <option value="30d">30 derniers jours</option>
              <option value="90d">3 derniers mois</option>
              <option value="1y">Dernière année</option>
            </select>
          </div>
          
          <div className="flex items-center space-x-2">
            <Filter className="w-4 h-4 text-gray-400" />
            <select
              value={selectedSupplier}
              onChange={(e) => setSelectedSupplier(e.target.value)}
              className="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="all">Tous les fournisseurs</option>
              <option value="sya">SYA Electronics</option>
              <option value="techsource">TechSource Pro</option>
              <option value="mobile">Mobile Discount</option>
            </select>
          </div>
        </div>
      </div>

      {/* KPIs Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {kpis.map((kpi) => {
          const Icon = kpi.icon;
          return (
            <div key={kpi.name} className="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-gray-600">{kpi.name}</p>
                  <p className="text-2xl font-bold text-gray-900 mt-2">{kpi.value}</p>
                  <div className="flex items-center mt-2">
                    <span className={`text-sm font-medium ${
                      kpi.changeType === 'increase' ? 'text-green-600' : 'text-red-600'
                    }`}>
                      {kpi.change}
                    </span>
                    <span className="text-sm text-gray-500 ml-1">vs période précédente</span>
                  </div>
                </div>
                <div className={`p-3 rounded-lg ${kpi.bgColor}`}>
                  <Icon className={`w-6 h-6 ${kpi.color}`} />
                </div>
              </div>
            </div>
          );
        })}
      </div>

      {/* Charts Row */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Monthly Evolution */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Évolution Mensuelle</h2>
          <div className="space-y-4">
            {monthlyData.map((data) => (
              <div key={data.month} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div className="flex items-center space-x-4">
                  <div className="font-medium text-gray-900 w-8">{data.month}</div>
                  <div className="text-sm text-gray-600">{data.products} produits</div>
                </div>
                <div className="flex items-center space-x-6">
                  <div className="text-right">
                    <div className="text-sm text-red-600">€{data.cost.toLocaleString('fr-FR')}</div>
                    <div className="text-xs text-gray-500">Coût</div>
                  </div>
                  <div className="text-right">
                    <div className="text-sm text-green-600">€{data.revenue.toLocaleString('fr-FR')}</div>
                    <div className="text-xs text-gray-500">Revenus</div>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Top Defects */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Top Pannes</h2>
          <div className="space-y-4">
            {topDefects.slice(0, 5).map((defect, index) => (
              <div key={defect.name} className="flex items-center">
                <div className="flex-1">
                  <div className="flex items-center justify-between mb-1">
                    <span className="text-sm font-medium text-gray-900">
                      {index + 1}. {defect.name}
                    </span>
                    <div className="flex items-center space-x-2">
                      <span className="text-sm text-gray-500">{defect.count}</span>
                      <span className={`text-xs ${
                        defect.trend.startsWith('+') ? 'text-red-600' : 'text-green-600'
                      }`}>
                        {defect.trend}
                      </span>
                    </div>
                  </div>
                  <div className="w-full bg-gray-200 rounded-full h-2">
                    <div
                      className="bg-red-500 h-2 rounded-full"
                      style={{ width: `${defect.percentage}%` }}
                    ></div>
                  </div>
                  <div className="text-xs text-gray-500 mt-1">{defect.percentage}%</div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Supplier Analysis */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 className="text-lg font-semibold text-gray-900 mb-6">Analyse par Fournisseur</h2>
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead>
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Fournisseur
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Produits
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Taux Fonctionnel
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Valeur Totale
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Marge Moyenne
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tendance
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {supplierStats.map((supplier) => (
                <tr key={supplier.name} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="flex items-center">
                      <Users className="w-5 h-5 text-gray-400 mr-3" />
                      <span className="font-medium text-gray-900">{supplier.name}</span>
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {supplier.totalProducts}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="flex items-center">
                      <span className={`text-sm font-medium ${
                        supplier.functionalRate >= 85 ? 'text-green-600' : 
                        supplier.functionalRate >= 80 ? 'text-orange-600' : 'text-red-600'
                      }`}>
                        {supplier.functionalRate}%
                      </span>
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    €{supplier.totalValue.toLocaleString('fr-FR')}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">
                    {supplier.avgMargin}%
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    {getTrendIcon(supplier.trend)}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Summary Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between mb-4">
            <h3 className="text-lg font-semibold text-gray-900">Rentabilité Globale</h3>
            <TrendingUp className="w-6 h-6 text-green-600" />
          </div>
          <div className="space-y-2">
            <div className="flex justify-between">
              <span className="text-gray-600">Coût d'achat total:</span>
              <span className="font-medium text-red-600">€186,450</span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-600">Valeur de revente:</span>
              <span className="font-medium text-green-600">€279,675</span>
            </div>
            <div className="flex justify-between border-t pt-2">
              <span className="font-medium text-gray-900">Marge brute:</span>
              <span className="font-bold text-green-600">€93,225 (50.0%)</span>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between mb-4">
            <h3 className="text-lg font-semibold text-gray-900">Qualité Produits</h3>
            <Package className="w-6 h-6 text-blue-600" />
          </div>
          <div className="space-y-2">
            <div className="flex justify-between">
              <span className="text-gray-600">Produits fonctionnels:</span>
              <span className="font-medium text-green-600">1,051 (84.3%)</span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-600">Produits défectueux:</span>
              <span className="font-medium text-red-600">196 (15.7%)</span>
            </div>
            <div className="flex justify-between border-t pt-2">
              <span className="font-medium text-gray-900">Total traité:</span>
              <span className="font-bold text-gray-900">1,247</span>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between mb-4">
            <h3 className="text-lg font-semibold text-gray-900">Performance</h3>
            <BarChart3 className="w-6 h-6 text-purple-600" />
          </div>
          <div className="space-y-2">
            <div className="flex justify-between">
              <span className="text-gray-600">Produits/jour (moy):</span>
              <span className="font-medium text-blue-600">41.6</span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-600">Temps traitement/produit:</span>
              <span className="font-medium text-blue-600">3.2 min</span>
            </div>
            <div className="flex justify-between border-t pt-2">
              <span className="font-medium text-gray-900">Efficacité:</span>
              <span className="font-bold text-green-600">+12% ce mois</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Statistics;