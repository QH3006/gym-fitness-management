import datetime
import ipaddress
from cryptography import x509
from cryptography.x509.oid import NameOID
from cryptography.hazmat.primitives import hashes
from cryptography.hazmat.primitives.asymmetric import rsa
from cryptography.hazmat.primitives import serialization

# 1. Tạo private key RSA 2048-bit
key = rsa.generate_private_key(
    public_exponent=65537,
    key_size=2048,
)

# 2. Thiết lập Subject theo đúng chuẩn Lab 1
subject = issuer = x509.Name([
    x509.NameAttribute(NameOID.COUNTRY_NAME, "VN"),
    x509.NameAttribute(NameOID.STATE_OR_PROVINCE_NAME, "ThaiNguyen"),
    x509.NameAttribute(NameOID.ORGANIZATION_NAME, "LabWebServer"),
    x509.NameAttribute(NameOID.COMMON_NAME, "gym.local"),
])

# 3. Tạo Certificate x509
cert = (
    x509.CertificateBuilder()
    .subject_name(subject)
    .issuer_name(issuer)
    .public_key(key.public_key())
    .serial_number(x509.random_serial_number())
    .not_valid_before(datetime.datetime.now(datetime.timezone.utc))
    .not_valid_after(datetime.datetime.now(datetime.timezone.utc) + datetime.timedelta(days=365))
    .add_extension(
        x509.SubjectAlternativeName([
            x509.DNSName("gym.local"),
            x509.DNSName("localhost"),
            x509.IPAddress(ipaddress.IPv4Address("127.0.0.1")),
        ]),
        critical=False,
    )
    .sign(key, hashes.SHA256())
)

# 4. Ghi private key
with open("d:/project/quan tri he thong/nginx/ssl/gym.local.key", "wb") as f:
    f.write(
        key.private_bytes(
            encoding=serialization.Encoding.PEM,
            format=serialization.PrivateFormat.TraditionalOpenSSL,
            encryption_algorithm=serialization.NoEncryption(),
        )
    )

# 5. Ghi certificate
with open("d:/project/quan tri he thong/nginx/ssl/gym.local.crt", "wb") as f:
    f.write(cert.public_bytes(serialization.Encoding.PEM))

print("Certificate and Key generated successfully!")
