using System;

namespace EczaneApi.Exceptions
{
    public class EczaneApiException : Exception
    {
        public int? StatusCode { get; }

        public EczaneApiException(string message, int? statusCode = null, Exception? innerException = null)
            : base(message, innerException)
        {
            StatusCode = statusCode;
        }

        public override string ToString()
        {
            return StatusCode.HasValue ? $"[HTTP {StatusCode}] {Message}" : Message;
        }
    }

    public class EczaneNotFoundException : EczaneApiException
    {
        public EczaneNotFoundException(string message) : base(message, 404)
        {
        }
    }
}
