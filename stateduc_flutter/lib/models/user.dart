/// User model — represents the authenticated server user.
///
/// From users.js / www/js/default.js:
///   Server JSON: { id, login, firstname, lastname, codeyear, libyear, filter, filters[] }
///   filters[] contains FilterPeriod objects: { CODE_TYPE_PERIOD, NAME_TYPE_PERIOD, ORDER_TYPE_PERIOD }
///
/// CRITICAL DISTINCTION (used throughout):
///   user.login  → used for reg_camp, save, reload endpoints
///   user.idUser → used for all other endpoints (new_camp, typ_reg_camp, etabs_camp, locs_camp, sys_camp)

class User {
  final String idUser;     // server: id         (numeric string)
  final String login;      // server: login      (username for auth + endpoints)
  final String nomUser;    // derived: "$lastname $firstname"
  final String codeyear;   // server: codeyear   (current year code)
  final String libyear;    // server: libyear    (current year label)
  final bool filter;       // server: filter     (true if user has filter periods)
  final List<FilterPeriod> filters; // server: filters[]

  User({
    required this.idUser,
    required this.login,
    required this.nomUser,
    required this.codeyear,
    required this.libyear,
    this.filter = false,
    this.filters = const [],
  });

  factory User.fromJson(Map<String, dynamic> json) {
    final firstname = (json['firstname'] ?? '').toString();
    final lastname  = (json['lastname']  ?? '').toString();
    return User(
      idUser:   (json['id'] ?? json['idUser'] ?? '').toString(),
      login:    (json['login'] ?? '').toString(),
      nomUser:  '$lastname $firstname'.trim(),
      codeyear: (json['codeyear'] ?? '').toString(),
      libyear:  (json['libyear'] ?? '').toString(),
      filter:   json['filter'] == true || json['filter'] == 1,
      filters:  json['filters'] != null
          ? (json['filters'] as List)
              .map((f) => FilterPeriod.fromJson(f))
              .toList()
          : [],
    );
  }

  Map<String, dynamic> toJson() => {
    'idUser':   idUser,
    'login':    login,
    'nomUser':  nomUser,
    'codeyear': codeyear,
    'libyear':  libyear,
    'filter':   filter,
    'filters':  filters.map((f) => f.toJson()).toList(),
  };
}

/// FilterPeriod — a data-entry period/filter (StmFilter from default.js).
///
/// From default.js: new StmFilter(id, nom, order)
/// Server JSON (in user.filters[]): { CODE_TYPE_PERIOD, NAME_TYPE_PERIOD, ORDER_TYPE_PERIOD }
/// DB schema: filter_periods (id_camp, id_filter, lib_filter)
class FilterPeriod {
  final String idFilter;   // server: CODE_TYPE_PERIOD,  DB: id_filter
  final String libFilter;  // server: NAME_TYPE_PERIOD,  DB: lib_filter
  final int    order;      // server: ORDER_TYPE_PERIOD  (sort order)

  FilterPeriod({
    required this.idFilter,
    required this.libFilter,
    this.order = 0,
  });

  factory FilterPeriod.fromJson(Map<String, dynamic> json) {
    return FilterPeriod(
      idFilter:  (json['CODE_TYPE_PERIOD'] ?? json['id_filter'] ?? '').toString(),
      libFilter: (json['NAME_TYPE_PERIOD'] ?? json['lib_filter'] ?? '').toString(),
      order:     int.tryParse(
          (json['ORDER_TYPE_PERIOD'] ?? json['order'] ?? '0').toString()) ?? 0,
    );
  }

  Map<String, dynamic> toJson() => {
    'CODE_TYPE_PERIOD':  idFilter,
    'NAME_TYPE_PERIOD':  libFilter,
    'ORDER_TYPE_PERIOD': order,
  };
}
