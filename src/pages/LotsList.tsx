import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { 
  Search, 
  Filter, 
  Package, 
  Eye, 
  Edit, 
  Calendar,
  DollarSign,
  Users,
  CheckCircle,
  Clock,
  AlertTriangle
} from 'lucide-react';

const LotsList = () => {
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState('all');

  const lots = [
    {
      id: 'LOT-2025-01-15-001',
      name: 'Achat Fournisseur SYA - 15/01/2025',
      supplier: 'SYA Electronics',
      status: 'En cours',
      date: '2025-01-15',
      totalProducts: 45,
      processedProducts: 23,
      costTotal: 12450,
      estimatedValue: 18675,
      functionalRate: 85
    },
    {
      id: 'LOT-2025-01-14-003',
      name: 'Lot iPhone Mix - 14/01/2025',
      supplier: 'TechSource Pro',
      status: 'Traité',
      date: '2025-01-14',
      totalProducts: 23,
      processedProducts: 23,
      costTotal: 8970,
      estimatedValue: 13455,
      functionalRate: 91
    },
    {
      id: 'LOT-2025-01-14-002',
      name: 'Samsung Galaxy Lot',
      supplier: 'Mobile Discount',
      status: 'En attente',
      date: '2025-01-14',
      totalProducts: 67,
      processedProducts: 0,
      costTotal: 15680,
      estimatedValue: 23520,
      functionalRate: 0
    },
    {
      id: 'LOT-2025-01-13-001',
      name: 'Tablettes et Accessoires',
      supplier: 'Digital Supply',
      status: 'Traité',
      date: '2025-01-13',
      totalProducts: 31,
      processedProducts: 31,
      costTotal: 6720,
      estimatedValue: 10080,
      functionalRate: 77
    },
    {
      id: 'LOT-2025-01-12-002',
      name: 'Lot de Reconditionnement',
      supplier: 'RefurbTech',
      status: 'Archivé',
      date: '2025-01-12',
      totalProducts: 89,
      processedProducts: 89,
      costTotal: 22340,
      estimatedValue: 33510,
      functionalRate: 82
    }
  ];

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'Traité':
        return <CheckCircle className="w-4 h-4 text-green-600" />;
      case 'En cours':
        return <Clock className="w-4 h-4 text-orange-600" />;
      case 'En attente':
        return <AlertTriangle className="w-4 h-4 text-yellow-600" />;
      default:
        return <Package className="w-4 h-4 text-gray-600" />;
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'Traité':
        return 'bg-green-100 text-green-800';
      case 'En cours':
        return 'bg-orange-100 text-orange-800';
      case 'En attente':
        return 'bg-yellow-100 text-yellow-800';
      case 'Archivé':
        return 'bg-gray-100 text-gray-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const filteredLots = lots.filter(lot => {
    const matchesSearch = lot.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         lot.supplier.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         lot.id.toLowerCase().includes(searchTerm.toLowerCase());
    
    const matchesStatus = statusFilter === 'all' || lot.status === statusFilter;
    
    return matchesSearch && matchesStatus;
  });

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Gestion des Lots</h1>
          <p className="text-gray-600">Suivez et gérez tous vos lots d'achat</p>
        </div>
        <Link
          to="/create-lot"
          className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center"
        >
          <Package className="w-4 h-4 mr-2" />
          Nouveau Lot
        </Link>
      </div>

      {/* Filters */}
      <div className="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <div className="flex flex-col sm:flex-row gap-4">
          <div className="flex-1 relative">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
            <input
              type="text"
              placeholder="Rechercher par nom, fournisseur ou ID..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div className="flex items-center space-x-2">
            <Filter className="w-4 h-4 text-gray-400" />
            <select
              value={statusFilter}
              onChange={(e) => setStatusFilter(e.target.value)}
              className="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="all">Tous les statuts</option>
              <option value="En attente">En attente</option>
              <option value="En cours">En cours</option>
              <option value="Traité">Traité</option>
              <option value="Archivé">Archivé</option>
            </select>
          </div>
        </div>
      </div>

      {/* Lots Table */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Lot
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Fournisseur
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Statut
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Produits
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Coût / Valeur
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Taux Fonctionnel
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {filteredLots.map((lot) => (
                <tr key={lot.id} className="hover:bg-gray-50 transition-colors">
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div>
                      <div className="text-sm font-medium text-gray-900">{lot.name}</div>
                      <div className="text-sm text-gray-500 flex items-center mt-1">
                        <Calendar className="w-3 h-3 mr-1" />
                        {new Date(lot.date).toLocaleDateString('fr-FR')}
                      </div>
                      <div className="text-xs text-gray-400 mt-1">{lot.id}</div>
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="flex items-center">
                      <Users className="w-4 h-4 text-gray-400 mr-2" />
                      <span className="text-sm text-gray-900">{lot.supplier}</span>
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="flex items-center">
                      {getStatusIcon(lot.status)}
                      <span className={`ml-2 px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(lot.status)}`}>
                        {lot.status}
                      </span>
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <div>
                      <div className="font-medium">{lot.processedProducts}/{lot.totalProducts}</div>
                      <div className="w-16 bg-gray-200 rounded-full h-1.5 mt-1">
                        <div
                          className="bg-blue-600 h-1.5 rounded-full"
                          style={{ width: `${(lot.processedProducts / lot.totalProducts) * 100}%` }}
                        ></div>
                      </div>
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <div>
                      <div className="flex items-center text-red-600">
                        <DollarSign className="w-3 h-3 mr-1" />
                        {lot.costTotal.toLocaleString('fr-FR')}€
                      </div>
                      <div className="flex items-center text-green-600 text-xs mt-1">
                        <DollarSign className="w-3 h-3 mr-1" />
                        {lot.estimatedValue.toLocaleString('fr-FR')}€
                      </div>
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    {lot.functionalRate > 0 ? (
                      <div className="flex items-center">
                        <span className={`text-sm font-medium ${
                          lot.functionalRate >= 80 ? 'text-green-600' : 
                          lot.functionalRate >= 60 ? 'text-orange-600' : 'text-red-600'
                        }`}>
                          {lot.functionalRate}%
                        </span>
                      </div>
                    ) : (
                      <span className="text-sm text-gray-400">-</span>
                    )}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div className="flex items-center space-x-2">
                      <Link
                        to={`/lots/${lot.id}`}
                        className="text-blue-600 hover:text-blue-700 flex items-center"
                      >
                        <Eye className="w-4 h-4 mr-1" />
                        Voir
                      </Link>
                      {lot.status === 'En cours' && (
                        <Link
                          to={`/lots/${lot.id}/qualification`}
                          className="text-green-600 hover:text-green-700 flex items-center"
                        >
                          <Edit className="w-4 h-4 mr-1" />
                          Traiter
                        </Link>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Summary */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div className="text-center">
            <div className="text-2xl font-bold text-gray-900">{filteredLots.length}</div>
            <div className="text-sm text-gray-500">Lots affichés</div>
          </div>
          <div className="text-center">
            <div className="text-2xl font-bold text-blue-600">
              {filteredLots.reduce((sum, lot) => sum + lot.totalProducts, 0)}
            </div>
            <div className="text-sm text-gray-500">Produits totaux</div>
          </div>
          <div className="text-center">
            <div className="text-2xl font-bold text-red-600">
              {filteredLots.reduce((sum, lot) => sum + lot.costTotal, 0).toLocaleString('fr-FR')}€
            </div>
            <div className="text-sm text-gray-500">Coût total</div>
          </div>
          <div className="text-center">
            <div className="text-2xl font-bold text-green-600">
              {filteredLots.reduce((sum, lot) => sum + lot.estimatedValue, 0).toLocaleString('fr-FR')}€
            </div>
            <div className="text-sm text-gray-500">Valeur estimée</div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default LotsList;