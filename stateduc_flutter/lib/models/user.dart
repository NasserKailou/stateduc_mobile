// User model - represents the authenticated server user

class User {
  final String id;
  final String login;
  final String firstname;
  final String lastname;
  final String codeyear;
  final String libyear;
  final bool filter;
  final List<FilterPeriod> filters;

  User({
    required this.id,
    required this.login,
    required this.firstname,
    required this.lastname,
    required this.codeyear,
    required this.libyear,
    this.filter = false,
    this.filters = const [],
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id']?.toString() ?? '',
      login: json['login'] ?? '',
      firstname: json['firstname'] ?? '',
      lastname: json['lastname'] ?? '',
      codeyear: json['codeyear']?.toString() ?? '',
      libyear: json['libyear'] ?? '',
      filter: json['filter'] == true || json['filter'] == 1,
      filters: json['filters'] != null
          ? (json['filters'] as List)
              .map((f) => FilterPeriod.fromJson(f))
              .toList()
          : [],
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'login': login,
        'firstname': firstname,
        'lastname': lastname,
        'codeyear': codeyear,
        'libyear': libyear,
        'filter': filter,
        'filters': filters.map((f) => f.toJson()).toList(),
      };
}

class FilterPeriod {
  final String id;
  final String name;
  final int order;

  FilterPeriod({
    required this.id,
    required this.name,
    required this.order,
  });

  factory FilterPeriod.fromJson(Map<String, dynamic> json) {
    return FilterPeriod(
      id: json['CODE_TYPE_PERIOD']?.toString() ?? '',
      name: json['NAME_TYPE_PERIOD'] ?? '',
      order: int.tryParse(json['ORDER_TYPE_PERIOD']?.toString() ?? '0') ?? 0,
    );
  }

  Map<String, dynamic> toJson() => {
        'CODE_TYPE_PERIOD': id,
        'NAME_TYPE_PERIOD': name,
        'ORDER_TYPE_PERIOD': order,
      };
}
