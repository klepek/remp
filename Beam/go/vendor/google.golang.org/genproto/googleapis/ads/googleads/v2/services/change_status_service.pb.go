// Code generated by protoc-gen-go. DO NOT EDIT.
// source: google/ads/googleads/v2/services/change_status_service.proto

package services

import (
	context "context"
	fmt "fmt"
	math "math"

	proto "github.com/golang/protobuf/proto"
	resources "google.golang.org/genproto/googleapis/ads/googleads/v2/resources"
	_ "google.golang.org/genproto/googleapis/api/annotations"
	grpc "google.golang.org/grpc"
)

// Reference imports to suppress errors if they are not otherwise used.
var _ = proto.Marshal
var _ = fmt.Errorf
var _ = math.Inf

// This is a compile-time assertion to ensure that this generated file
// is compatible with the proto package it is being compiled against.
// A compilation error at this line likely means your copy of the
// proto package needs to be updated.
const _ = proto.ProtoPackageIsVersion3 // please upgrade the proto package

// Request message for '[ChangeStatusService.GetChangeStatus][google.ads.googleads.v2.services.ChangeStatusService.GetChangeStatus]'.
type GetChangeStatusRequest struct {
	// The resource name of the change status to fetch.
	ResourceName         string   `protobuf:"bytes,1,opt,name=resource_name,json=resourceName,proto3" json:"resource_name,omitempty"`
	XXX_NoUnkeyedLiteral struct{} `json:"-"`
	XXX_unrecognized     []byte   `json:"-"`
	XXX_sizecache        int32    `json:"-"`
}

func (m *GetChangeStatusRequest) Reset()         { *m = GetChangeStatusRequest{} }
func (m *GetChangeStatusRequest) String() string { return proto.CompactTextString(m) }
func (*GetChangeStatusRequest) ProtoMessage()    {}
func (*GetChangeStatusRequest) Descriptor() ([]byte, []int) {
	return fileDescriptor_1fc807e084a0ffab, []int{0}
}

func (m *GetChangeStatusRequest) XXX_Unmarshal(b []byte) error {
	return xxx_messageInfo_GetChangeStatusRequest.Unmarshal(m, b)
}
func (m *GetChangeStatusRequest) XXX_Marshal(b []byte, deterministic bool) ([]byte, error) {
	return xxx_messageInfo_GetChangeStatusRequest.Marshal(b, m, deterministic)
}
func (m *GetChangeStatusRequest) XXX_Merge(src proto.Message) {
	xxx_messageInfo_GetChangeStatusRequest.Merge(m, src)
}
func (m *GetChangeStatusRequest) XXX_Size() int {
	return xxx_messageInfo_GetChangeStatusRequest.Size(m)
}
func (m *GetChangeStatusRequest) XXX_DiscardUnknown() {
	xxx_messageInfo_GetChangeStatusRequest.DiscardUnknown(m)
}

var xxx_messageInfo_GetChangeStatusRequest proto.InternalMessageInfo

func (m *GetChangeStatusRequest) GetResourceName() string {
	if m != nil {
		return m.ResourceName
	}
	return ""
}

func init() {
	proto.RegisterType((*GetChangeStatusRequest)(nil), "google.ads.googleads.v2.services.GetChangeStatusRequest")
}

func init() {
	proto.RegisterFile("google/ads/googleads/v2/services/change_status_service.proto", fileDescriptor_1fc807e084a0ffab)
}

var fileDescriptor_1fc807e084a0ffab = []byte{
	// 385 bytes of a gzipped FileDescriptorProto
	0x1f, 0x8b, 0x08, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0xff, 0x84, 0x92, 0xc1, 0x4a, 0xeb, 0x40,
	0x14, 0x86, 0x49, 0x2e, 0x5c, 0xb8, 0xe1, 0x5e, 0x2e, 0x44, 0xd0, 0x12, 0x5d, 0x94, 0xda, 0x85,
	0x74, 0x31, 0x23, 0x11, 0x45, 0x46, 0xbb, 0x48, 0x5d, 0xd4, 0x95, 0x94, 0x16, 0xba, 0x90, 0x40,
	0x19, 0x93, 0x21, 0x06, 0x9a, 0x99, 0x9a, 0x33, 0xe9, 0x46, 0xdc, 0xf8, 0x0a, 0xbe, 0x81, 0x4b,
	0xd7, 0x3e, 0x45, 0xb7, 0xbe, 0x82, 0x1b, 0x7d, 0x09, 0x25, 0x99, 0x4c, 0x5a, 0xb5, 0xa5, 0xbb,
	0x9f, 0x73, 0xce, 0xf7, 0x9f, 0x39, 0x7f, 0x62, 0x9d, 0x46, 0x42, 0x44, 0x63, 0x86, 0x69, 0x08,
	0x58, 0xc9, 0x5c, 0x4d, 0x5d, 0x0c, 0x2c, 0x9d, 0xc6, 0x01, 0x03, 0x1c, 0x5c, 0x53, 0x1e, 0xb1,
	0x11, 0x48, 0x2a, 0x33, 0x18, 0x95, 0x65, 0x34, 0x49, 0x85, 0x14, 0x76, 0x5d, 0x21, 0x88, 0x86,
	0x80, 0x2a, 0x1a, 0x4d, 0x5d, 0xa4, 0x69, 0xe7, 0x70, 0x95, 0x7f, 0xca, 0x40, 0x64, 0xe9, 0x8f,
	0x05, 0xca, 0xd8, 0xd9, 0xd1, 0xd8, 0x24, 0xc6, 0x94, 0x73, 0x21, 0xa9, 0x8c, 0x05, 0xd7, 0xdd,
	0xad, 0x85, 0x6e, 0x30, 0x8e, 0x19, 0x97, 0xaa, 0xd1, 0x68, 0x5b, 0x9b, 0x5d, 0x26, 0xcf, 0x0a,
	0xc3, 0x41, 0xe1, 0xd7, 0x67, 0x37, 0x19, 0x03, 0x69, 0xef, 0x5a, 0xff, 0xf4, 0xc6, 0x11, 0xa7,
	0x09, 0xab, 0x19, 0x75, 0x63, 0xef, 0x4f, 0xff, 0xaf, 0x2e, 0x5e, 0xd0, 0x84, 0xb9, 0x6f, 0x86,
	0xb5, 0xb1, 0x08, 0x0f, 0xd4, 0x15, 0xf6, 0xb3, 0x61, 0xfd, 0xff, 0xe6, 0x6b, 0x1f, 0xa3, 0x75,
	0xb7, 0xa3, 0xe5, 0x4f, 0x71, 0xf0, 0x4a, 0xb2, 0xca, 0x04, 0x2d, 0x72, 0x8d, 0xa3, 0xfb, 0x97,
	0xd7, 0x07, 0x73, 0xdf, 0x46, 0x79, 0x6e, 0xb7, 0x5f, 0xce, 0x68, 0x07, 0x19, 0x48, 0x91, 0xb0,
	0x14, 0x70, 0xab, 0x0c, 0x52, 0x41, 0xb8, 0x75, 0xe7, 0x6c, 0xcf, 0xbc, 0xda, 0xdc, 0xbf, 0x54,
	0x93, 0x18, 0x50, 0x20, 0x92, 0xce, 0x87, 0x61, 0x35, 0x03, 0x91, 0xac, 0xbd, 0xa2, 0x53, 0x5b,
	0x92, 0x48, 0x2f, 0x4f, 0xbb, 0x67, 0x5c, 0x9e, 0x97, 0x74, 0x24, 0xc6, 0x94, 0x47, 0x48, 0xa4,
	0x11, 0x8e, 0x18, 0x2f, 0xbe, 0x05, 0x9e, 0xef, 0x5b, 0xfd, 0x73, 0x9d, 0x68, 0xf1, 0x68, 0xfe,
	0xea, 0x7a, 0xde, 0x93, 0x59, 0xef, 0x2a, 0x43, 0x2f, 0x04, 0xa4, 0x64, 0xae, 0x86, 0x2e, 0x2a,
	0x17, 0xc3, 0x4c, 0x8f, 0xf8, 0x5e, 0x08, 0x7e, 0x35, 0xe2, 0x0f, 0x5d, 0x5f, 0x8f, 0xbc, 0x9b,
	0x4d, 0x55, 0x27, 0xc4, 0x0b, 0x81, 0x90, 0x6a, 0x88, 0x90, 0xa1, 0x4b, 0x88, 0x1e, 0xbb, 0xfa,
	0x5d, 0xbc, 0xf3, 0xe0, 0x33, 0x00, 0x00, 0xff, 0xff, 0xe9, 0x1a, 0xcb, 0x67, 0x03, 0x03, 0x00,
	0x00,
}

// Reference imports to suppress errors if they are not otherwise used.
var _ context.Context
var _ grpc.ClientConn

// This is a compile-time assertion to ensure that this generated file
// is compatible with the grpc package it is being compiled against.
const _ = grpc.SupportPackageIsVersion4

// ChangeStatusServiceClient is the client API for ChangeStatusService service.
//
// For semantics around ctx use and closing/ending streaming RPCs, please refer to https://godoc.org/google.golang.org/grpc#ClientConn.NewStream.
type ChangeStatusServiceClient interface {
	// Returns the requested change status in full detail.
	GetChangeStatus(ctx context.Context, in *GetChangeStatusRequest, opts ...grpc.CallOption) (*resources.ChangeStatus, error)
}

type changeStatusServiceClient struct {
	cc *grpc.ClientConn
}

func NewChangeStatusServiceClient(cc *grpc.ClientConn) ChangeStatusServiceClient {
	return &changeStatusServiceClient{cc}
}

func (c *changeStatusServiceClient) GetChangeStatus(ctx context.Context, in *GetChangeStatusRequest, opts ...grpc.CallOption) (*resources.ChangeStatus, error) {
	out := new(resources.ChangeStatus)
	err := c.cc.Invoke(ctx, "/google.ads.googleads.v2.services.ChangeStatusService/GetChangeStatus", in, out, opts...)
	if err != nil {
		return nil, err
	}
	return out, nil
}

// ChangeStatusServiceServer is the server API for ChangeStatusService service.
type ChangeStatusServiceServer interface {
	// Returns the requested change status in full detail.
	GetChangeStatus(context.Context, *GetChangeStatusRequest) (*resources.ChangeStatus, error)
}

func RegisterChangeStatusServiceServer(s *grpc.Server, srv ChangeStatusServiceServer) {
	s.RegisterService(&_ChangeStatusService_serviceDesc, srv)
}

func _ChangeStatusService_GetChangeStatus_Handler(srv interface{}, ctx context.Context, dec func(interface{}) error, interceptor grpc.UnaryServerInterceptor) (interface{}, error) {
	in := new(GetChangeStatusRequest)
	if err := dec(in); err != nil {
		return nil, err
	}
	if interceptor == nil {
		return srv.(ChangeStatusServiceServer).GetChangeStatus(ctx, in)
	}
	info := &grpc.UnaryServerInfo{
		Server:     srv,
		FullMethod: "/google.ads.googleads.v2.services.ChangeStatusService/GetChangeStatus",
	}
	handler := func(ctx context.Context, req interface{}) (interface{}, error) {
		return srv.(ChangeStatusServiceServer).GetChangeStatus(ctx, req.(*GetChangeStatusRequest))
	}
	return interceptor(ctx, in, info, handler)
}

var _ChangeStatusService_serviceDesc = grpc.ServiceDesc{
	ServiceName: "google.ads.googleads.v2.services.ChangeStatusService",
	HandlerType: (*ChangeStatusServiceServer)(nil),
	Methods: []grpc.MethodDesc{
		{
			MethodName: "GetChangeStatus",
			Handler:    _ChangeStatusService_GetChangeStatus_Handler,
		},
	},
	Streams:  []grpc.StreamDesc{},
	Metadata: "google/ads/googleads/v2/services/change_status_service.proto",
}
