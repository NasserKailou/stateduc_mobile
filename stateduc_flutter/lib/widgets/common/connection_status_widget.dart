import 'package:flutter/material.dart';
import 'package:connectivity_plus/connectivity_plus.dart';

/// ConnectionStatusWidget — shows a colored dot/icon indicating online/offline
/// status. Mirrors stmDevice.isOnline() from script.js (but actually works
/// correctly, unlike the original which always returned true).
///
/// Usage: Place in AppBar.actions or as a persistent banner.
class ConnectionStatusWidget extends StatefulWidget {
  const ConnectionStatusWidget({super.key, this.showLabel = false});
  final bool showLabel;

  @override
  State<ConnectionStatusWidget> createState() =>
      _ConnectionStatusWidgetState();
}

class _ConnectionStatusWidgetState
    extends State<ConnectionStatusWidget> {
  bool _isOnline = true;

  @override
  void initState() {
    super.initState();
    _checkConnectivity();
    Connectivity().onConnectivityChanged.listen((results) {
      setState(() {
        _isOnline = results.isNotEmpty &&
            results.first != ConnectivityResult.none;
      });
    });
  }

  Future<void> _checkConnectivity() async {
    final results = await Connectivity().checkConnectivity();
    if (mounted) {
      setState(() {
        _isOnline = results.isNotEmpty &&
            results.first != ConnectivityResult.none;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final color = _isOnline ? Colors.green : Colors.red;
    final icon =
        _isOnline ? Icons.wifi : Icons.wifi_off;
    final label = _isOnline ? 'En ligne' : 'Hors ligne';

    if (widget.showLabel) {
      return Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 16, color: color),
          const SizedBox(width: 4),
          Text(label,
              style: TextStyle(
                  color: color,
                  fontSize: 12,
                  fontWeight: FontWeight.w500)),
        ],
      );
    }
    return Tooltip(
      message: label,
      child: Icon(icon, size: 20, color: color),
    );
  }
}

/// OfflineBanner — displayed at the top of data entry screens when offline,
/// reminding the user that sends will be queued.
class OfflineBanner extends StatelessWidget {
  const OfflineBanner({super.key});

  @override
  Widget build(BuildContext context) {
    return StreamBuilder<List<ConnectivityResult>>(
      stream: Connectivity().onConnectivityChanged,
      builder: (context, snapshot) {
        final results = snapshot.data ?? [];
        final isOffline = results.isEmpty ||
            results.first == ConnectivityResult.none;
        if (!isOffline) return const SizedBox.shrink();
        return Container(
          width: double.infinity,
          color: Colors.orange.shade700,
          padding: const EdgeInsets.symmetric(
              horizontal: 12, vertical: 6),
          child: const Row(
            children: [
              Icon(Icons.wifi_off, color: Colors.white, size: 16),
              SizedBox(width: 8),
              Expanded(
                child: Text(
                  'Mode hors ligne — les données seront envoyées à la reconnexion',
                  style: TextStyle(
                      color: Colors.white, fontSize: 12),
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}
